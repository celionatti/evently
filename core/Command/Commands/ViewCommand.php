<?php

declare(strict_types=1);

namespace Trees\Command\Commands;

use Trees\Command\Command;
use Trees\Command\TermUI;
use Exception;

class ViewCommand extends Command
{
    protected string $templatesDir;
    protected string $viewsDir;

    protected function configure(): void
    {
        $this->name = 'make:view';
        $this->description = 'Create a new view template';

        // Set up directories
        $this->templatesDir = dirname(__DIR__) . '/templates';
        $this->viewsDir = ROOT_PATH . '/resources/views';

        // Command configuration
        $this->addArgument('name', 'The name/path of the view to create', true)
             ->addOption('model', 'm', 'The model to use for creating resource views', true)
             ->addOption('layout', 'l', 'The layout to extend (default: app)', true)
             ->addOption('resource', 'r', 'Create a complete set of resource views')
             ->addOption('force', 'f', 'Overwrite existing view files');
    }

    public function handle(array $arguments, array $options): int
    {
        try {
            $viewName = $arguments['name'];
            $isResource = $options['resource'] ?? false;
            $force = $options['force'] ?? false;

            // Validate paths
            $this->validatePaths();

            if ($isResource) {
                if (empty($options['model'])) {
                    throw new Exception("Model name is required when creating resource views. Use --model option.");
                }

                $modelName = $this->normalizeModelName($options['model']);
                return $this->createResourceViews($modelName, $options, $force);
            }

            return $this->createSingleView($viewName, $options, $force);
        } catch (Exception $e) {
            TermUI::error($e->getMessage());
            return 1;
        }
    }

    protected function validatePaths(): void
    {
        if (!is_dir($this->templatesDir)) {
            mkdir($this->templatesDir, 0755, true);
            TermUI::info("Created templates directory: {$this->templatesDir}");
        }

        if (!is_dir($this->viewsDir)) {
            mkdir($this->viewsDir, 0755, true);
            TermUI::info("Created views directory: {$this->viewsDir}");
        }
    }

    protected function createSingleView(string $viewName, array $options, bool $force): int
    {
        $viewPath = $this->normalizeViewPath($viewName);
        $layoutName = $options['layout'] ?? 'app';
        $fullViewPath = $this->viewsDir . '/' . $viewPath . '.php';

        // Create directory if needed
        $viewDirectoryPath = dirname($fullViewPath);
        if (!is_dir($viewDirectoryPath)) {
            mkdir($viewDirectoryPath, 0755, true);
        }

        // Check if view exists
        if (file_exists($fullViewPath) && !$force) {
            throw new Exception("View {$viewPath} already exists. Use --force to overwrite.");
        }

        // Get template content
        $template = $this->getViewTemplate('view');

        // Prepare replacements
        $replacements = [
            '{{ViewName}}' => $viewPath,
            '{{LayoutName}}' => $layoutName,
            '{{Date}}' => date('Y-m-d'),
            '{{Year}}' => date('Y'),
        ];

        // Generate and save view
        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        file_put_contents($fullViewPath, $content);
        TermUI::success("View created successfully: {$fullViewPath}");

        return 0;
    }

    protected function createResourceViews(string $modelName, array $options, bool $force): int
    {
        $layoutName = $options['layout'] ?? 'app';
        $viewDirectoryName = strtolower($this->pluralize($modelName));
        $viewDirectoryPath = $this->viewsDir . '/' . $viewDirectoryName;

        // Create model views directory
        if (!is_dir($viewDirectoryPath)) {
            mkdir($viewDirectoryPath, 0755, true);
        }

        // View types to create
        $viewTypes = ['index', 'create', 'edit', 'show'];
        $createdCount = 0;

        foreach ($viewTypes as $viewType) {
            $viewPath = "{$viewDirectoryName}/{$viewType}";
            $fullViewPath = $this->viewsDir . '/' . $viewPath . '.php';

            // Skip if exists and not forcing
            if (file_exists($fullViewPath)) {
                if (!$force) {
                    TermUI::warning("View {$viewPath} already exists. Skipping.");
                    continue;
                }
                TermUI::warning("Overwriting view: {$viewPath}");
            }

            // Get template
            $template = $this->getViewTemplate("view_{$viewType}");

            // Prepare replacements
            $replacements = [
                '{{ViewName}}' => $viewPath,
                '{{LayoutName}}' => $layoutName,
                '{{ModelName}}' => $modelName,
                '{{modelName}}' => lcfirst($modelName),
                '{{pluralModelName}}' => $viewDirectoryName,
                '{{Date}}' => date('Y-m-d'),
                '{{Year}}' => date('Y'),
            ];

            // Generate and save view
            $content = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $template
            );

            file_put_contents($fullViewPath, $content);
            $createdCount++;
            TermUI::info("Created {$viewType} view: {$fullViewPath}");
        }

        TermUI::success("Created {$createdCount} resource views for {$modelName}");
        return 0;
    }

    protected function getViewTemplate(string $templateName): string
    {
        $templatePath = $this->templatesDir . '/' . $templateName . '.php.template';

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        // Fallback to default templates
        $method = 'getDefault' . ucfirst(str_replace('view_', '', $templateName)) . 'ViewTemplate';
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return $this->getDefaultViewTemplate();
    }

    protected function normalizeViewPath(string $path): string
    {
        return str_replace('.', '/', $path);
    }

    protected function normalizeModelName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9]/', ' ', $name);
        $name = str_replace(' ', '', ucwords($name));

        // Remove "Model" suffix if present
        if (str_ends_with($name, 'Model')) {
            $name = substr($name, 0, -5);
        }

        return $name;
    }

    protected function pluralize(string $word): string
    {
        $lastChar = strtolower($word[strlen($word) - 1]);
        $lastTwoChars = substr(strtolower($word), -2);

        $exceptions = [
            'child' => 'children',
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'tooth' => 'teeth',
            'foot' => 'feet',
            'mouse' => 'mice',
            'goose' => 'geese',
        ];

        if (array_key_exists(strtolower($word), $exceptions)) {
            return $exceptions[strtolower($word)];
        }

        if ($lastChar === 'y' && !in_array($lastTwoChars, ['ay', 'ey', 'iy', 'oy', 'uy'])) {
            return substr($word, 0, -1) . 'ies';
        }

        if (in_array($lastChar, ['s', 'x', 'z']) || in_array($lastTwoChars, ['ch', 'sh'])) {
            return $word . 'es';
        }

        return $word . 's';
    }

    protected function getDefaultViewTemplate(): string
    {
        return <<<'EOT'
<?php
/**
 * View: {{ViewName}}
 * Created: {{Date}}
 */
?>

@section('styles')
<style>
    h1 {
        color: teal;
    }
</style>
@endsection

@section('content')
<h1>Welcome to Trees Page. {{$name}}</h1>
@endsection
EOT;
    }

    protected function getDefaultIndexViewTemplate(): string
    {
        return <<<'EOT'
<?php
/**
 * View: {{ViewName}}
 * Created: {{Date}}
 */
?>

@section('styles')
<style>
    .table {
        margin-top: 20px;
    }
    .actions {
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ModelName}} List</h1>
        <a href="/{{pluralModelName}}/create" class="btn btn-primary">Create New {{ModelName}}</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->created_at->format('Y-m-d') }}</td>
                    <td class="actions">
                        <a href="/{{pluralModelName}}/{{ $item->id }}" class="btn btn-sm btn-info">View</a>
                        <a href="/{{pluralModelName}}/{{ $item->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                        <form method="POST" action="/{{pluralModelName}}/{{ $item->id }}" class="d-inline">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach

                @if($items->isEmpty())
                <tr>
                    <td colspan="4" class="text-center">No {{ModelName}} records found.</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
EOT;
    }

    protected function getDefaultCreateViewTemplate(): string
    {
        return <<<'EOT'
<?php
/**
 * View: {{ViewName}}
 * Created: {{Date}}
 */
?>

@section('styles')
<style>
    .card {
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2>Create New {{ModelName}}</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="/{{pluralModelName}}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Create {{ModelName}}</button>
                            <a href="/{{pluralModelName}}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOT;
    }

    protected function getDefaultEditViewTemplate(): string
    {
        return <<<'EOT'
<?php
/**
 * View: {{ViewName}}
 * Created: {{Date}}
 */
?>

@section('styles')
<style>
    .card {
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h2>Edit {{ModelName}}</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="/{{pluralModelName}}/{{ $item->id }}">
                        @method('PUT')
                        @csrf
                        <div class="form-group mb-3">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $item->name) }}" required>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $item->description) }}</textarea>
                            @error('description')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">Update {{ModelName}}</button>
                            <a href="/{{pluralModelName}}/{{ $item->id }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOT;
    }

    protected function getDefaultShowViewTemplate(): string
    {
        return <<<'EOT'
<?php
/**
 * View: {{ViewName}}
 * Created: {{Date}}
 */
?>

@section('styles')
<style>
    .card {
        margin-top: 20px;
    }
    dt {
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h2>{{ModelName}} Details</h2>
                        <div>
                            <a href="/{{pluralModelName}}/{{ $item->id }}/edit" class="btn btn-warning">Edit</a>
                            <form method="POST" action="/{{pluralModelName}}/{{ $item->id }}" class="d-inline">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">ID</dt>
                        <dd class="col-sm-9">{{ $item->id }}</dd>

                        <dt class="col-sm-3">Name</dt>
                        <dd class="col-sm-9">{{ $item->name }}</dd>

                        <dt class="col-sm-3">Description</dt>
                        <dd class="col-sm-9">{{ $item->description }}</dd>

                        <dt class="col-sm-3">Created At</dt>
                        <dd class="col-sm-9">{{ $item->created_at->format('Y-m-d H:i:s') }}</dd>

                        <dt class="col-sm-3">Updated At</dt>
                        <dd class="col-sm-9">{{ $item->updated_at->format('Y-m-d H:i:s') }}</dd>
                    </dl>

                    <div class="mt-4">
                        <a href="/{{pluralModelName}}" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
EOT;
    }
}