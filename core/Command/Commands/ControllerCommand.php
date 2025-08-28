<?php

declare(strict_types=1);

namespace Trees\Command\Commands;

use Trees\Command\Command;
use Trees\Command\TermUI;
use Exception;

class ControllerCommand extends Command
{
    protected string $templatesDir;
    protected string $controllersDir;

    protected function configure(): void
    {
        $this->name = 'make:controller';
        $this->description = 'Create a new controller class';

        // Set up directories
        $this->templatesDir = dirname(__DIR__) . '/templates';
        $this->controllersDir = ROOT_PATH . '/app/controllers';

        // Command configuration
        $this->addArgument('name', 'The name of the controller to create', true)
             ->addOption('resource', 'r', 'Create a resource controller with CRUD actions')
             ->addOption('model', 'm', 'The model that this controller will use', true)
             ->addOption('api', 'a', 'Create an API controller')
             ->addOption('force', 'f', 'Overwrite existing controller file');
    }

    public function handle(array $arguments, array $options): int
    {
        try {
            $controllerName = $this->normalizeControllerName($arguments['name']);
            $isResource = $options['resource'] ?? false;
            $isApi = $options['api'] ?? false;
            $force = $options['force'] ?? false;

            // Determine model name
            $modelName = $options['model'] ?? $this->guessModelName($controllerName);
            $modelName = $this->normalizeModelName($modelName);

            // Validate paths
            $this->validatePaths();

            // Check if controller exists
            $controllerPath = $this->getControllerPath($controllerName);
            if (file_exists($controllerPath)) {
                if (!$force) {
                    throw new Exception("Controller {$controllerName} already exists. Use --force to overwrite.");
                }
                TermUI::warning("Overwriting existing controller: {$controllerName}");
            }

            // Create controller
            $this->generateController(
                $controllerName,
                $modelName,
                $isResource,
                $isApi,
                $controllerPath
            );

            TermUI::success("Controller created successfully!");
            TermUI::info("Path: {$controllerPath}");

            return 0;
        } catch (Exception $e) {
            TermUI::error($e->getMessage());
            return 1;
        }
    }

    protected function normalizeControllerName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9]/', ' ', $name);
        $name = str_replace(' ', '', ucwords($name));

        if (!str_ends_with($name, 'Controller')) {
            $name .= 'Controller';
        }

        return $name;
    }

    protected function normalizeModelName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9]/', ' ', $name);
        $name = str_replace(' ', '', ucwords($name));

        // Remove "Model" suffix if present and add it back properly
        if (str_ends_with($name, 'Model')) {
            $name = substr($name, 0, -5);
        }

        return $name;
    }

    protected function validatePaths(): void
    {
        if (!is_dir($this->templatesDir)) {
            mkdir($this->templatesDir, 0755, true);
            TermUI::info("Created templates directory: {$this->templatesDir}");
        }

        if (!is_dir($this->controllersDir)) {
            mkdir($this->controllersDir, 0755, true);
            TermUI::info("Created controllers directory: {$this->controllersDir}");
        }
    }

    protected function getControllerPath(string $controllerName): string
    {
        return $this->controllersDir . '/' . $controllerName . '.php';
    }

    protected function guessModelName(string $controllerName): string
    {
        $baseName = str_replace('Controller', '', $controllerName);
        return $this->singularize($baseName);
    }

    protected function generateController(
        string $controllerName,
        string $modelName,
        bool $isResource,
        bool $isApi,
        string $controllerPath
    ): void {
        $template = $this->getTemplate($isResource, $isApi);

        $replacements = [
            '{{ControllerName}}' => $controllerName,
            '{{ModelName}}' => $modelName,
            '{{ModelVariable}}' => lcfirst($modelName),
            '{{ModelPlural}}' => $this->pluralize($modelName),
            '{{ModelPluralLower}}' => strtolower($this->pluralize($modelName)),
            '{{Namespace}}' => 'App\\controllers',
            '{{ModelNamespace}}' => 'App\\models',
            '{{Date}}' => date('Y-m-d'),
            '{{Year}}' => date('Y'),
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        file_put_contents($controllerPath, $content);
    }

    protected function getTemplate(bool $isResource, bool $isApi): string
    {
        $templatePath = $this->templatesDir . '/' . $this->getTemplateFilename($isResource, $isApi);

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        if ($isResource && $isApi) {
            return $this->getApiResourceControllerTemplate();
        } elseif ($isResource) {
            return $this->getResourceControllerTemplate();
        } elseif ($isApi) {
            return $this->getApiControllerTemplate();
        }

        return $this->getBaseControllerTemplate();
    }

    protected function getTemplateFilename(bool $isResource, bool $isApi): string
    {
        if ($isResource && $isApi) {
            return 'api_resource_controller.php.template';
        } elseif ($isResource) {
            return 'resource_controller.php.template';
        } elseif ($isApi) {
            return 'api_controller.php.template';
        }
        return 'controller.php.template';
    }

    protected function getBaseControllerTemplate(): string
    {
        return <<<'EOT'
<?php

declare(strict_types=1);

namespace {{Namespace}};

use Trees\Controller\Controller;
use Trees\Http\Request;
use Trees\Http\Response;

class {{ControllerName}} extends Controller
{
    /**
     * Display the index page
     */
    public function index(Request $request): Response
    {
        return $this->render('{{ModelPluralLower}}/index');
    }
}
EOT;
    }

    protected function getResourceControllerTemplate(): string
    {
        return <<<'EOT'
<?php

declare(strict_types=1);

namespace {{Namespace}};

use Trees\Controller\Controller;
use Trees\Http\Request;
use Trees\Http\Response;
use {{ModelNamespace}}\{{ModelName}};

class {{ControllerName}} extends Controller
{
    public function index(Request $request): Response
    {
        ${{ModelVariable}}s = {{ModelName}}::all();

        return $this->render('{{ModelPluralLower}}/index', [
            '{{ModelPluralLower}}' => ${{ModelVariable}}s,
        ]);
    }

    public function create(Request $request): Response
    {
        return $this->render('{{ModelPluralLower}}/create');
    }

    public function store(Request $request): Response
    {
        $validated = $this->validate($request, [
            // Add validation rules
        ]);

        ${{ModelVariable}} = new {{ModelName}}();
        ${{ModelVariable}}->fill($validated);
        ${{ModelVariable}}->save();

        return $this->redirect('/{{ModelPluralLower}}')
            ->withSuccess('{{ModelName}} created successfully!');
    }

    public function show(Request $request, int $id): Response
    {
        ${{ModelVariable}} = {{ModelName}}::find($id);

        if (!${{ModelVariable}}) {
            return $this->abort(404);
        }

        return $this->render('{{ModelPluralLower}}/show', [
            '{{ModelVariable}}}' => ${{ModelVariable}},
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        ${{ModelVariable}} = {{ModelName}}::find($id);

        if (!${{ModelVariable}}) {
            return $this->abort(404);
        }

        return $this->render('{{ModelPluralLower}}/edit', [
            '{{ModelVariable}}}' => ${{ModelVariable}},
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        ${{ModelVariable}} = {{ModelName}}::find($id);

        if (!${{ModelVariable}}) {
            return $this->abort(404);
        }

        $validated = $this->validate($request, [
            // Add validation rules
        ]);

        ${{ModelVariable}}->fill($validated);
        ${{ModelVariable}}->save();

        return $this->redirect('/{{ModelPluralLower}}/' . $id)
            ->withSuccess('{{ModelName}} updated successfully!');
    }

    public function destroy(Request $request, int $id): Response
    {
        ${{ModelVariable}} = {{ModelName}}::find($id);

        if (!${{ModelVariable}}) {
            return $this->abort(404);
        }

        ${{ModelVariable}}->delete();

        return $this->redirect('/{{ModelPluralLower}}')
            ->withSuccess('{{ModelName}} deleted successfully!');
    }
}
EOT;
    }

    protected function getApiControllerTemplate(): string
    {
        return <<<'EOT'
<?php

declare(strict_types=1);

namespace {{Namespace}};

use Trees\Controller\Controller;
use Trees\Http\Request;
use Trees\Http\Response;

class {{ControllerName}} extends Controller
{
    public function index(Request $request): Response
    {
        return $this->json([
            'message' => 'API endpoint'
        ]);
    }
}
EOT;
    }

    protected function getApiResourceControllerTemplate(): string
    {
        return <<<'EOT'
<?php

declare(strict_types=1);

namespace {{Namespace}};

use Trees\Controller\Controller;
use Trees\Http\Request;
use Trees\Http\Response;
use {{ModelNamespace}}\{{ModelName}};

class {{ControllerName}} extends Controller
{
    public function index(Request $request): Response
    {
        ${{ModelVariable}}s = {{ModelName}}::all();

        return $this->json([
            'data' => ${{ModelVariable}}s,
        ]);
    }

    public function store(Request $request): Response
    {
        $data = $request->getJson();

        // Add validation
        ${{ModelVariable}} = new {{ModelName}}();
        ${{ModelVariable}}->fill($data);
        ${{ModelVariable}}->save();

        return $this->json([
            'data' => ${{ModelVariable}},
        ], 201);
    }

    public function show(Request $request, int $id): Response
    {
        ${{ModelVariable}} = {{ModelName}}::find($id);

        if (!${{ModelVariable}}) {
            return $this->json([
                'error' => 'Not found'
            ], 404);
        }

        return $this->json([
            'data' => ${{ModelVariable}},
        ]);
    }

    public function update(Request $request, int $id): Response
    {
        ${{ModelVariable}} = {{ModelName}}::find($id);

        if (!${{ModelVariable}}) {
            return $this->json([
                'error' => 'Not found'
            ], 404);
        }

        $data = $request->getJson();
        ${{ModelVariable}}->fill($data);
        ${{ModelVariable}}->save();

        return $this->json([
            'data' => ${{ModelVariable}},
        ]);
    }

    public function destroy(Request $request, int $id): Response
    {
        ${{ModelVariable}} = {{ModelName}}::find($id);

        if (!${{ModelVariable}}) {
            return $this->json([
                'error' => 'Not found'
            ], 404);
        }

        ${{ModelVariable}}->delete();

        return $this->json([
            'message' => 'Deleted successfully'
        ]);
    }
}
EOT;
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

    protected function singularize(string $word): string
    {
        $exceptions = [
            'children' => 'child',
            'people' => 'person',
            'men' => 'man',
            'women' => 'woman',
            'teeth' => 'tooth',
            'feet' => 'foot',
            'mice' => 'mouse',
            'geese' => 'goose',
        ];

        if (array_key_exists(strtolower($word), $exceptions)) {
            return $exceptions[strtolower($word)];
        }

        if (str_ends_with($word, 'ies')) {
            return substr($word, 0, -3) . 'y';
        }

        if (str_ends_with($word, 'es')) {
            $base = substr($word, 0, -2);
            $lastChar = substr($base, -1);

            if (in_array($lastChar, ['s', 'x', 'z']) ||
                str_ends_with($base, 'ch') ||
                str_ends_with($base, 'sh')) {
                return $base;
            }
        }

        if (str_ends_with($word, 's')) {
            return substr($word, 0, -1);
        }

        return $word;
    }
}