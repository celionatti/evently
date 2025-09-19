<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Advertisement;
use App\Models\Article;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Helper\FlashMessages\FlashMessage;

class ArticleController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('default');
        $name = "Eventlyy";
        $this->view->setTitle("Articles | {$name}");
    }

    public function articles(Request $request, Response $response)
    {
        // Build query options
        $queryOptions = [
            'per_page' => $request->query('per_page', 12),
            'page' => $request->query('page', 1),
            'order_by' => ['created_at' => 'DESC']
        ];

        // Only show active articles to the public
        $conditions = ['status' => 'publish'];

        // Add search functionality
        $search = $request->query('search');
        if (!empty($search)) {
            // Use the applySearch method from the Article model
            $queryOptions['search'] = $search;
        }

        $queryOptions['conditions'] = $conditions;

        // Get events with pagination
        $articlesData = Article::paginate($queryOptions);

        // Create pagination instance
        $pagination = new Paginator($articlesData['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $advertisements = Advertisement::where(['is_active' => '1']);

        $view = [
            'articles' => $articlesData['data'],
            'pagination' => $paginationLinks,
            'currentSearch' => $search,
            'totalArticles' => $articlesData['meta']['total'] ?? 0,
            'advertisements' => $advertisements
        ];

        return $this->render('articles', $view);
    }

    public function article(Request $request, Response $response, $id, $slug)
    {
        // Find article by slug or ID
        $article = null;

        // Try to find by slug first, then by ID
        if (is_numeric($id)) {
            $article = Article::find($id);
        } else {
            // Find by slug
            $articles = Article::where(['slug' => $slug]);
            $article = !empty($articles) ? $articles[0] : null;
        }

        if (!$article) {
            FlashMessage::setMessage("Article not found!", 'danger');
            return $response->redirect("/articles");
        }
    }
}