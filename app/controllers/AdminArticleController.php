<?php

declare(strict_types=1);

namespace App\controllers;

use App\models\Article;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Logger\Logger;
use Trees\Helper\Support\Image;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Helper\Support\FileUploader;
use Trees\Helper\FlashMessages\FlashMessage;
use Trees\Database\QueryBuilder\QueryBuilder;
use Trees\Database\Database;

class AdminArticleController extends Controller
{
    protected $uploader;
    protected ?Article $articleModel;
    protected const MAX_UPLOAD_FILES = 1;
    protected const UPLOAD_DIR = 'uploads/articles/';

    public function onConstruct()
    {
        requireAuth();
        if (!isAdminOrOrganiser()) {
            FlashMessage::setMessage("Access denied. Admin or Organiser privileges required.", 'danger');
            return redirect("/");
        }
        $this->view->setLayout('admin');
        $imageProcessor = new Image();
        $this->articleModel = new Article();
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin | Articles");
        $this->uploader = new FileUploader(
            uploadDir: self::UPLOAD_DIR,
            maxFileSize: 5 * 1024 * 1024,
            allowedMimeTypes: ['image/jpg', 'image/jpeg', 'image/png', 'image/webp', 'image/gif'],
            overwriteExisting: false,
            imageProcessor: $imageProcessor,
            maxImageWidth: 1200,
            maxImageHeight: 800,
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
        // For organiser, only show their own articles
        // For admin, show all articles
        $queryOptions = [
            'per_page' => $request->query('per_page', 10),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ];

        if (isOrganiser()) {
            // Organiser can only see their own articles
            $queryOptions['conditions'] = ['user_id' => auth()->id];
        }

        $articles = $this->articleModel::paginate($queryOptions);

        // Create pagination instance
        $pagination = new Paginator($articles['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'articles' => $articles['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/articles/manage', $view);
    }

    public function view(Request $request, Response $response, $slug)
    {
        $article = Article::findBySlug($slug);

        if (!$article) {
            FlashMessage::setMessage("Article Not Found!", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        // Check if organiser is trying to view someone else's article
        if (isOrganiser() && $article->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only view your own articles.", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        $view = [
            'article' => $article
        ];

        return $this->render('admin/articles/view', $view);
    }

    public function create()
    {
        return $this->render('admin/articles/create');
    }

    public function insert(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'title' => 'required|min:3',
            'content' => 'required|min:50',
            'meta_title' => 'required|min:3',
            'meta_description' => 'required|min:10',
            'meta_keywords' => 'required',
            'image' => 'file|mimes:image/jpg,image/jpeg,image/png|maxSize:5120|min:1|max:' . self::MAX_UPLOAD_FILES,
            'status' => 'required|in:draft,publish'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/articles/create");
        }

        try {
            $data = $request->all();

            // Generate slug
            $articleSlug = str_slug($data['title'], "-");
            $data['slug'] = $articleSlug;
            $data['user_id'] = auth()->id;

            // Handle file upload
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $uploadedFile = $this->uploader->uploadFromRequest($request, 'image');
                if ($uploadedFile !== null) {
                    $data['image'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
                }
            } else {
                // Image is required, so this shouldn't happen if validation passed
                throw new \RuntimeException('Article image is required');
            }

            $articleId = Article::create($data);

            if (!$articleId || $articleId === false) {
                throw new \RuntimeException('Article creation failed');
            }

            FlashMessage::setMessage("New Article Created Successfully!");
            return $response->redirect("/admin/articles/manage");
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/articles/create");
        } catch (\RuntimeException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/articles/create");
        } catch (\Exception $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Unexpected error occurred.", 'danger');
            return $response->redirect("/admin/articles/create");
        }
    }

    public function edit(Request $request, Response $response, $slug)
    {
        $article = Article::findBySlug($slug);

        if (!$article) {
            FlashMessage::setMessage("Article Not Found!", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        // Check if organiser is trying to edit someone else's article
        if (isOrganiser() && $article->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only edit your own articles.", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        $view = [
            'article' => $article
        ];

        return $this->render('admin/articles/edit', $view);
    }

    public function update(Request $request, Response $response, $slug)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $article = Article::findBySlug($slug);
        if (!$article) {
            FlashMessage::setMessage("Article Not Found!", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        // Check if organiser is trying to update someone else's article
        if (isOrganiser() && $article->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only update your own articles.", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        $rules = [
            'title' => 'required|min:3',
            'content' => 'required|min:50',
            'meta_title' => 'required|min:3',
            'meta_description' => 'required|min:10',
            'meta_keywords' => 'required',
            'image' => 'file|mimes:image/jpg,image/jpeg,image/png|maxSize:5120|max:' . self::MAX_UPLOAD_FILES,
            'status' => 'required|in:draft,publish'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/articles/edit/{$slug}");
        }

        try {
            $data = $request->all();

            // Generate slug if title changed
            if ($data['title'] !== $article->title) {
                $articleSlug = str_slug($data['title'], "-");
                $data['slug'] = $articleSlug;
            }

            // Handle file upload
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $uploadedFile = $this->uploader->uploadFromRequest($request, 'image');
                if ($uploadedFile !== null) {
                    // Delete old image if exists
                    if ($article->image && file_exists(ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $article->image)) {
                        @unlink(ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $article->image);
                    }
                    $data['image'] = str_replace(ROOT_PATH . '/public', '', $uploadedFile);
                }
            }

            $updated = $article->updateInstance($data);
            if (!$updated) {
                throw new \RuntimeException('Article update failed');
            }

            FlashMessage::setMessage("Article Updated Successfully!");
            return $response->redirect("/admin/articles/manage");
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/articles/edit/{$slug}");
        }
    }

    public function delete(Request $request, Response $response, $slug)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $article = Article::findBySlug($slug);

        if (!$article) {
            FlashMessage::setMessage("Article Not Found!", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        // Check if organiser is trying to delete someone else's article
        if (isOrganiser() && $article->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only delete your own articles.", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        try {
            // Store the image path BEFORE deletion
            $imagePath = ROOT_PATH . '/public' . DIRECTORY_SEPARATOR . $article->image;

            // Delete the article
            if (!$article->delete()) {
                throw new \RuntimeException('Failed to delete article');
            }

            // Delete article image file AFTER successful database operation
            if ($imagePath && file_exists($imagePath) && is_file($imagePath)) {
                if (!@unlink($imagePath)) {
                    // Log the error but don't fail the entire operation
                    Logger::warning("Failed to delete article image: " . $imagePath);
                }
            }

            FlashMessage::setMessage("Article deleted successfully!");
            return $response->redirect("/admin/articles/manage");
        } catch (TreesException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/articles/manage");
        } catch (\RuntimeException $e) {
            FlashMessage::setMessage("Deletion Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/articles/manage");
        }
    }

    public function articleStatus(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $slug = $request->input('article_slug');

        $article = Article::findBySlug($slug);

        if (!$article) {
            FlashMessage::setMessage("Article Not Found!", 'danger');
            return $response->redirect("/admin/articles/view/{$slug}");
        }

        // Check if organiser is trying to update someone else's article
        if (isOrganiser() && $article->user_id !== auth()->id) {
            FlashMessage::setMessage("Access denied. You can only update your own articles.", 'danger');
            return $response->redirect("/admin/articles/manage");
        }

        try {
            $updateData = [
                'status' => $request->input('status', $article->status)
            ];
            $updated = Article::updateWhere(['id' => $article->id], $updateData);
            if ($updated) {
                FlashMessage::setMessage("Article status updated successfully!", 'success');
            } else {
                FlashMessage::setMessage("No changes made to article status.", 'info');
            }
            return $response->redirect("/admin/articles/view/{$slug}");
        } catch (\Exception $e) {
            FlashMessage::setMessage("Error updating article status: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/articles/view/{$slug}");
        }
    }

    public function __destruct()
    {
        $this->articleModel = null;
        $this->uploader = null;
    }
}