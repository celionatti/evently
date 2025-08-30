<?php

declare(strict_types=1);

namespace App\controllers;

use Trees\Http\Request;
use Trees\Http\Response;
use App\models\Categories;
use Trees\Pagination\Paginator;
use Trees\Controller\Controller;
use Trees\Exception\TreesException;
use Trees\Helper\FlashMessages\FlashMessage;

class AdminCategoryController extends Controller
{
    public function onConstruct()
    {
        $this->view->setLayout('admin');
        $name = "Eventlyy";
        $this->view->setTitle("{$name} Admin Category | Dashboard");
    }

    public function manage(Request $request, Response $response)
    {
        $categories = Categories::paginate([
            'per_page' => $request->query('per_page', 5),
            'page' => $request->query('page', 1),
            'order_by' => ['id' => 'DESC']
        ]);

        $pagination = new Paginator($categories['meta']);
        $paginationLinks = $pagination->render('bootstrap');

        $view = [
            'categories' => $categories['data'],
            'pagination' => $paginationLinks
        ];

        return $this->render('admin/categories/manage', $view);
    }

    public function create()
    {
        $view = [];

        return $this->render('admin/categories/create', $view);
    }

    public function insert(Request $request, Response $response)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $rules = [
            'name' => 'required|min:3|unique:categories.name',
            'description' => 'required|min:10|string',
            'status' => 'required'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/categories/create");
        }

        try {
            $data = $request->all();
            $data['slug'] = str_slug($data['name'], "_");

            $category = Categories::create($data);

            if(!$category) {
                throw new \RuntimeException('Category creation failed');
            }
            FlashMessage::setMessage("New Category Created!");
            return $response->redirect("/admin/categories/manage");
        } catch (TreesException $e) {
        set_form_data($request->all());
            FlashMessage::setMessage("Creation Failed! Please try again. Error: " . $e->getMessage(), 'danger');
            return $response->redirect("/admin/categories/create");
        }
    }

    public function edit(Request $request, Response $response, $slug)
    {
        $category = Categories::findBySlug($slug);

        if (!$category) {
            FlashMessage::setMessage("Category Not Found!", 'danger');
            return $response->redirect("/admin/categories/manage");
        }

        $view = [
            'category' => $category
        ];

        return $this->render('admin/categories/edit', $view);
    }

    public function update(Request $request, Response $response, $slug)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $category = Categories::findBySlug($slug);

        if (!$category) {
            FlashMessage::setMessage("Category Not Found!", 'danger');
            return $response->redirect("/admin/categories/manage");
        }

        $rules = [
            'name' => "required|min:3|unique:categories.name, name!={$category->name}",
            'description' => 'required|min:10|string',
            'status' => 'required'
        ];

        if (!$request->validate($rules, false)) {
            set_form_data($request->all());
            set_form_error($request->getErrors());
            return $response->redirect("/admin/categories/edit/{$slug}");
        }

        try {
            $data = $request->all();
            if ($category->update($data)) {
                FlashMessage::setMessage("Category Updated!");
                return $response->redirect("/admin/categories/manage");
            }
            throw new \RuntimeException('Update operation failed');
        } catch (TreesException $e) {
            set_form_data($request->all());
            FlashMessage::setMessage("Update Failed! Please try again. Error: " . $e->getMessage(), "danger");
            return $response->redirect("/admin/categories/edit/{$slug}");
        }
    }

    public function delete(Request $request, Response $response, $slug)
    {
        if ("POST" !== $request->getMethod()) {
            return;
        }

        $category = Categories::findBySlug($slug);

        if (!$category) {
            FlashMessage::setMessage("Category Not Found!", 'danger');
            return $response->redirect("/admin/categories/manage");
        }

        try {
            if ($category->delete()) {
                FlashMessage::setMessage("Category Deleted!");
                return $response->redirect("/admin/categories/manage");
            }

            throw new \RuntimeException('Delete operation failed');
        } catch (\Exception $e) {
            FlashMessage::setMessage("Delete Failed! Please try again.", "danger");
            return $response->redirect("/admin/categories/manage");
        }
    }
}
