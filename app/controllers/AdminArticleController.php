<?php

declare(strict_types=1);

namespace App\controllers;

use App\Models\Article;
use Trees\Http\Request;
use Trees\Http\Response;
use Trees\Helper\Support\Image;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Helper\Support\FileUploader;
use Trees\Helper\FlashMessages\FlashMessage;

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

    public function manage(Request $request, Response $response): string
    {
        $this->view->setTitle("Admin Eventlyy | Manage Articles");

        $view = [];

        return $this->render('admin/articles/manage', $view);
    }
}