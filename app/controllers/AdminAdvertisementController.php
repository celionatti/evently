<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Logger\Logger;
use Trees\Database\Database;
use App\models\Advertisement;
use Trees\Helper\Support\Image;
use Trees\Pagination\Paginator;
use App\controllers\BaseController;
use Trees\Exception\TreesException;
use Trees\Helper\Support\FileUploader;
use Trees\Helper\FlashMessages\FlashMessage;
use Trees\Database\QueryBuilder\QueryBuilder;

class AdminAdvertisementController extends BaseController
{
    protected $uploader;
    protected ?Advertisement $advertisementModel;
    protected const MAX_UPLOAD_FILES = 1;
    protected const UPLOAD_DIR = 'uploads/advertisements/';

    public function onConstruct()
    {
        parent::onConstruct();

        $this->view->setLayout('admin');

        // Set meta tags for articles listing
        $this->view->setAuthor("Eventlyy Team | Eventlyy")
            ->setKeywords("events, tickets, event management, conferences, workshops, meetups, event planning");

        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin or Organiser privileges required.", 'danger');
            return redirect("/");
        }
        $imageProcessor = new Image();
        $this->advertisementModel = new Advertisement();
        $this->uploader = new FileUploader(
            uploadDir: self::UPLOAD_DIR,
            maxFileSize: 5 * 1024 * 1024,
            allowedMimeTypes: ['image/jpg', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            overwriteExisting: false,
            imageProcessor: $imageProcessor,
            maxImageWidth: 1200,
            maxImageHeight: 1080,
            imageQuality: 85
        );

        $this->uploader->setQualitySettings(
            75, // JPEG quality
            85, // WebP quality
            6,  // PNG compression
            true // Convert to WebP
        );
    }

    public function manage(Request $request, Response $response)
    {
        $this->view->setTitle("Eventlyy | Manage Advertisement");

        $queryOptions = [
            'per_page' => $request->query('per_page', 10),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ];

        if (isOrganiser()) {
            // Organiser can only see their own advertisements
            $queryOptions['conditions'] = ['user_id' => auth()->id];
        }

        $advertisements = $this->advertisementModel::paginate($queryOptions);

        // Create pagination instance
        $pagination = new Paginator($advertisements['meta']);

        // Render the pagination links
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'advertisements' => $advertisements['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/advertisements/manage', $view);
    }

    public function view(Request $request, Response $response, $id)
    {
        $advertisement = Advertisement::find($id);

        if (!$advertisement) {
            FlashMessage::setMessage("Advertisement Not Found!", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        // Check if organiser is trying to view someone else's advertisement
        if (isOrganiser() && $advertisement->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only view your own advertisements.", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        // Calculate performance metrics
        $ctr = $advertisement->impressions > 0 ?
            round(($advertisement->clicks / $advertisement->impressions) * 100, 2) : 0;

        // Determine campaign status
        $now = new \DateTime();
        $startDate = new \DateTime($advertisement->start_date);
        $endDate = new \DateTime($advertisement->end_date);

        $campaignStatus = 'pending';
        if ($now > $endDate) {
            $campaignStatus = 'expired';
        } elseif ($now >= $startDate && $now <= $endDate) {
            $campaignStatus = 'running';
        }

        // Calculate days remaining
        $daysRemaining = $now <= $endDate ?
            $now->diff($endDate)->days : 0;

        $this->view->setTitle("Eventlyy | Advertisement - {$advertisement->title}");

        $view = [
            'advertisement' => $advertisement,
            'performance' => [
                'impressions' => $advertisement->impressions,
                'clicks' => $advertisement->clicks,
                'ctr' => $ctr,
                'campaign_status' => $campaignStatus,
                'days_remaining' => $daysRemaining
            ]
        ];

        return $this->render('admin/advertisements/view', $view);
    }

    public function create()
    {
        $this->view->setTitle("Eventlyy | Create New Advertisement");

        return $this->render('admin/advertisements/create');
    }

    public function insert(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
            'target_url' => 'url',
            'ad_type' => 'required|in:landscape,portrait',
            'priority' => 'numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_featured' => 'in:0,1',
            'is_active' => 'in:0,1'
        ];

        // Handle image validation based on upload type
        $uploadType = $request->input('upload_type', 'file');
        if ($uploadType === 'file') {
            $rules['image_file'] = 'file|mimes:image/jpg,image/jpeg,image/png,image/webp,image/gif|maxSize:5120|min:1|max:' . self::MAX_UPLOAD_FILES;
        } else {
            $rules['image_url'] = 'required|url';
        }

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/advertisements/create");
        }

        // Validate date logic
        $startDate = new \DateTime($request->input('start_date'));
        $endDate = new \DateTime($request->input('end_date'));

        if ($endDate <= $startDate) {
            set_form_data($request->all());
            set_form_error(['end_date' => ['End date must be after start date']]);
            return $response->redirect("/admin/advertisements/create");
        }

        try {
            $data = $request->all();
            $data['user_id'] = auth()->id;

            // Convert boolean fields
            $data['is_featured'] = isset($data['is_featured']) && $data['is_featured'] == '1' ? 1 : 0;
            $data['is_active'] = isset($data['is_active']) && $data['is_active'] == '1' ? 1 : 0;

            // Set default priority if not provided
            $data['priority'] = isset($data['priority']) ? (int)$data['priority'] : 0;

            // Handle image based on upload type
            if ($uploadType === 'file') {
                // Handle file upload
                if ($request->hasFile('image_file') && $request->file('image_file')->isValid()) {
                    $uploadedFile = $this->uploader->uploadFromRequest($request, 'image_file');
                    if ($uploadedFile !== null) {
                        $data['image_url'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
                    }
                }
                unset($data['image_file'], $data['upload_type']);
            } else {
                // Use provided URL
                // $data['image_url'] = $data['image_url'];
                unset($data['image_file'], $data['upload_type']);
            }

            $advertisementId = Advertisement::create($data);

            if (!$advertisementId || $advertisementId === false) {
                throw new \RuntimeException('Advertisement creation failed');
            }

            FlashMessage::setMessage("New Advertisement Created Successfully!");
            return $response->redirect("/admin/advertisements/manage");
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/advertisements/create");
        } catch (\RuntimeException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/advertisements/create");
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Unexpected error occurred.", 'danger');
            return $response->redirect("/admin/advertisements/create");
        }
    }

    public function edit(Request $request, Response $response, $id)
    {
        $advertisement = Advertisement::find($id);

        if (!$advertisement) {
            FlashMessage::setMessage("Advertisement Not Found!", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        // Check if organiser is trying to edit someone else's advertisement
        if (isOrganiser() && $advertisement->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only edit your own advertisements.", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        $this->view->setTitle("Eventlyy | Update Advertisement - {$advertisement->title}");

        $view = [
            'advertisement' => $advertisement
        ];

        return $this->render('admin/advertisements/edit', $view);
    }

    public function update(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $advertisement = Advertisement::find($id);
        if (!$advertisement) {
            FlashMessage::setMessage("Advertisement Not Found!", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        // Check if organiser is trying to update someone else's advertisement
        if (isOrganiser() && $advertisement->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only update your own advertisements.", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        $rules = [
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10',
            'target_url' => 'url',
            'ad_type' => 'required|in:landscape,portrait',
            'priority' => 'numeric|min:0|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'is_featured' => 'in:0,1',
            'is_active' => 'in:0,1'
        ];

        // Handle image validation based on upload type (only if uploading new image)
        $uploadType = $request->input('upload_type', 'file');
        if ($uploadType === 'file' && $request->hasFile('image_file')) {
            $rules['image_file'] = 'file|mimes:image/jpg,image/jpeg,image/png,image/webp,image/gif|maxSize:5120|max:' . self::MAX_UPLOAD_FILES;
        } elseif ($uploadType === 'url' && $request->input('image_url')) {
            $rules['image_url'] = 'url';
        }

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/advertisements/edit/{$id}");
        }

        // Validate date logic
        $startDate = new \DateTime($request->input('start_date'));
        $endDate = new \DateTime($request->input('end_date'));

        if ($endDate <= $startDate) {
            set_form_data($request->all());
            set_form_error(['end_date' => ['End date must be after start date']]);
            return $response->redirect("/admin/advertisements/edit/{$id}");
        }

        try {
            $data = $request->all();

            // Convert boolean fields
            $data['is_featured'] = isset($data['is_featured']) && $data['is_featured'] == '1' ? 1 : 0;
            $data['is_active'] = isset($data['is_active']) && $data['is_active'] == '1' ? 1 : 0;

            // Set priority
            $data['priority'] = isset($data['priority']) ? (int)$data['priority'] : 0;

            // Handle image update based on upload type
            if ($uploadType === 'file' && $request->hasFile('image_file') && $request->file('image_file')->isValid()) {
                // Upload new file
                $uploadedFile = $this->uploader->uploadFromRequest($request, 'image_file');
                if ($uploadedFile !== null) {
                    // Delete old image if it's a local file
                    if ($advertisement->image_url && !filter_var($advertisement->image_url, FILTER_VALIDATE_URL)) {
                        $oldImagePath = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $advertisement->image_url;
                        if (file_exists($oldImagePath)) {
                            @unlink($oldImagePath);
                        }
                    }
                    $data['image_url'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
                }
            } elseif ($uploadType === 'url' && $request->input('image_url') && $request->input('image_url') !== $advertisement->image_url) {
                // Delete old local image if switching to URL
                if ($advertisement->image_url && !filter_var($advertisement->image_url, FILTER_VALIDATE_URL)) {
                    $oldImagePath = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $advertisement->image_url;
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                $data['image_url'] = $request->input('image_url');
            }

            // Remove non-database fields
            unset($data['image_file'], $data['upload_type']);

            $updated = $advertisement->updateInstance($data);
            if (!$updated) {
                throw new \RuntimeException('Advertisement update failed');
            }

            FlashMessage::setMessage("Advertisement Updated Successfully!");
            return $response->redirect("/admin/advertisements/manage");
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/advertisements/edit/{$id}");
        } catch (\RuntimeException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/advertisements/edit/{$id}");
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Unexpected error occurred.", 'danger');
            return $response->redirect("/admin/advertisements/edit/{$id}");
        }
    }

    public function delete(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $advertisement = Advertisement::find($id);

        if (!$advertisement) {
            FlashMessage::setMessage("Advertisement Not Found!", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        // Check if organiser is trying to delete someone else's advertisement
        if (isOrganiser() && $advertisement->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only delete your own advertisements.", 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }

        try {
            // Store the image path BEFORE deletion
            $imagePath = null;
            if ($advertisement->image_url && !filter_var($advertisement->image_url, FILTER_VALIDATE_URL)) {
                $imagePath = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $advertisement->image_url;
            }

            // Delete the advertisement
            if (!$advertisement->delete()) {
                throw new \RuntimeException('Failed to delete advertisement');
            }

            // Delete image file AFTER successful database deletion (only if it's a local file)
            if ($imagePath && file_exists($imagePath) && is_file($imagePath)) {
                if (!@unlink($imagePath)) {
                    // Log the error but don't fail the entire operation
                    Logger::warning("Failed to delete advertisement image: " . $imagePath);
                }
            }

            FlashMessage::setMessage("Advertisement deleted successfully!");
            return $response->redirect("/admin/advertisements/manage");
        } catch (TreesException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/advertisements/manage");
        } catch (\RuntimeException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/advertisements/manage");
        }
    }

    public function toggleStatus(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $advertisement = Advertisement::find($id);

        if (!$advertisement) {
            return $response->json(['success' => false, 'message' => 'Advertisement not found'], 404);
        }

        // Check if organiser is trying to toggle someone else's advertisement
        if (isOrganiser() && $advertisement->user_id !== auth()->id) {
            return $response->json(['success' => false, 'message' => 'Access denied. You can only modify your own advertisements.'], 403);
        }

        try {
            $newStatus = $request->input('is_active', $advertisement->is_active) ? 1 : 0;

            $updateData = ['is_active' => $newStatus];
            $updated = $advertisement->updateInstance($updateData);

            if ($updated) {
                $statusText = $newStatus ? 'activated' : 'deactivated';
                return $response->json([
                    'success' => true,
                    'message' => "Advertisement {$statusText} successfully",
                    'new_status' => $newStatus
                ]);
            } else {
                return $response->json(['success' => false, 'message' => 'No changes made to advertisement status'], 400);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error updating advertisement status: ' . $e->getMessage()], 500);
        }
    }

    public function recordClick(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $advertisement = Advertisement::find($id);

        if (!$advertisement || !$advertisement->is_active) {
            return $response->json(['success' => false, 'message' => 'Advertisement not found or inactive'], 404);
        }

        try {
            // Increment click count
            $newClickCount = $advertisement->clicks + 1;
            $updated = Advertisement::updateWhere(['id' => $id], ['clicks' => $newClickCount]);

            if ($updated) {
                return $response->json([
                    'success' => true,
                    'message' => 'Click recorded',
                    'target_url' => $advertisement->target_url
                ]);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to record click'], 500);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error recording click'], 500);
        }
    }

    public function recordImpression(Request $request, Response $response, $id)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $advertisement = Advertisement::find($id);

        if (!$advertisement || !$advertisement->is_active) {
            return $response->json(['success' => false, 'message' => 'Advertisement not found or inactive'], 404);
        }

        try {
            // Increment impression count
            $newImpressionCount = $advertisement->impressions + 1;
            $updated = Advertisement::updateWhere(['id' => $id], ['impressions' => $newImpressionCount]);

            if ($updated) {
                return $response->json(['success' => true, 'message' => 'Impression recorded']);
            } else {
                return $response->json(['success' => false, 'message' => 'Failed to record impression'], 500);
            }
        } catch (\Exception $e) {
            return $response->json(['success' => false, 'message' => 'Error recording impression'], 500);
        }
    }

    /**
     * Route handler for cleanup operations
     */
    public function cleanupPage(Request $request, Response $response)
    {
        $monthsOld = $request->query('months', 6);
        $stats = $this->getOldAdvertisementsStats($monthsOld);

        $view = [
            'stats' => $stats,
            'months_old' => $monthsOld
        ];

        return $this->render('admin/advertisements/cleanup', $view);
    }

    /**
     * Route handler for executing cleanup
     */
    public function executeCleanup(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return $response->redirect("/admin/advertisements/cleanup");
        }

        $monthsOld = $request->input('months_old', 6);
        $dryRun = $request->input('dry_run', false);

        // Security check - only admin can perform cleanup
        if (!isAdmin()) {
            FlashMessage::setMessage("Access denied. Only administrators can perform cleanup operations.", 'danger');
            return $response->redirect("/admin/advertisements/cleanup");
        }

        $result = $this->cleanupOldAdvertisements($monthsOld, $dryRun);

        if ($result['success']) {
            FlashMessage::setMessage($result['message'], 'success');
        } else {
            FlashMessage::setMessage($result['message'], 'danger');
        }

        return $response->redirect("/admin/advertisements/cleanup");
    }

    public function getOldAdvertisementsStats($monthsOld = 6)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$monthsOld} months"));

        // Get old advertisements from database
        $oldAds = Advertisement::where(['end_date <' => $cutoffDate]);

        $totalAds = count($oldAds);
        $totalClicks = 0;
        $totalImpressions = 0;
        $estimatedSize = 0;
        $adsData = [];

        foreach ($oldAds as $ad) {
            $totalClicks += $ad->clicks;
            $totalImpressions += $ad->impressions;

            // Estimate image file size (for local files only)
            if (!empty($ad->image_url) && !filter_var($ad->image_url, FILTER_VALIDATE_URL)) {
                $imagePath = ROOT_PATH . '/public' . $ad->image_url;
                if (file_exists($imagePath)) {
                    $estimatedSize += filesize($imagePath);
                }
            }

            // Add advertisement data for display
            $adsData[] = [
                'title' => $ad->title,
                'end_date' => $ad->end_date,
                'id' => $ad->id,
                'clicks' => $ad->clicks,
                'impressions' => $ad->impressions,
                'ad_type' => $ad->ad_type
            ];
        }

        return [
            'count' => $totalAds,
            'total_advertisements' => $totalAds,
            'total_clicks' => $totalClicks,
            'total_impressions' => $totalImpressions,
            'estimated_freed_space' => $this->formatBytes($estimatedSize),
            'cutoff_date' => $cutoffDate,
            'advertisements' => $adsData
        ];
    }

    public function cleanupOldAdvertisements($monthsOld = 6, $dryRun = true)
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$monthsOld} months"));

        // Use QueryBuilder to get advertisements with proper condition
        $db = Database::getInstance();
        $builder = new QueryBuilder($db);

        $oldAdsData = $builder->table('advertisements')
            ->where('end_date', $cutoffDate, '<')
            ->get();

        $totalAds = count($oldAdsData);
        $deletedAds = 0;

        if ($totalAds === 0) {
            return [
                'success' => true,
                'message' => "No advertisements older than {$monthsOld} months to clean up."
            ];
        }

        // Convert data to Advertisement models for easier handling
        $oldAds = [];
        foreach ($oldAdsData as $adData) {
            $ad = new Advertisement();
            $ad->fill($adData);
            $ad->exists = true;
            $ad->original = $adData;
            $oldAds[] = $ad;
        }

        foreach ($oldAds as $ad) {
            try {
                if (!$dryRun) {
                    // Store image path before deletion
                    $imagePath = null;
                    if (!empty($ad->image_url) && !filter_var($ad->image_url, FILTER_VALIDATE_URL)) {
                        $imagePath = ROOT_PATH . '/public' . $ad->image_url;
                    }

                    // Delete the advertisement
                    if ($ad->delete()) {
                        $deletedAds++;

                        // Delete image file after successful database deletion (only local files)
                        if ($imagePath && file_exists($imagePath) && is_file($imagePath)) {
                            if (!@unlink($imagePath)) {
                                Logger::warning("Failed to delete advertisement image: " . $imagePath);
                            }
                        }
                    } else {
                        throw new \RuntimeException('Failed to delete advertisement: ' . $ad->id);
                    }
                }
            } catch (TreesException $e) {
                return [
                    'success' => false,
                    'message' => "Cleanup failed: " . $e->getMessage()
                ];
            } catch (\RuntimeException $e) {
                return [
                    'success' => false,
                    'message' => "Cleanup failed: " . $e->getMessage()
                ];
            } catch (\Exception $e) {
                return [
                    'success' => false,
                    'message' => "Cleanup failed: " . $e->getMessage()
                ];
            }
        }

        $message = $dryRun
            ? "Dry Run: Found {$totalAds} advertisements older than {$monthsOld} months."
            : "Cleanup Complete: Deleted {$deletedAds} advertisements older than {$monthsOld} months.";

        return [
            'success' => true,
            'message' => $message,
            'stats' => [
                'deleted_advertisements' => $deletedAds
            ]
        ];
    }

    // Helper method to format bytes
    private function formatBytes($size, $precision = 2)
    {
        if ($size == 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($size, 1024);

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $units[floor($base)];
    }

    public function __destruct()
    {
        $this->advertisementModel = null;
        $this->uploader = null;
    }
}
