<?php

declare(strict_types=1);

namespace Trees\Command\Commands;

use Trees\Command\Command;
use Trees\Command\TermUI;
use Exception;

class ModelCommand extends Command
{
    protected string $templatesDir;
    protected string $modelsDir;
    protected string $migrationsDir;

    protected function configure(): void
    {
        $this->name = 'make:model';
        $this->description = 'Create a new model class';

        // Set up directories
        $this->templatesDir = dirname(__DIR__) . '/templates';
        $this->modelsDir = ROOT_PATH . '/app/models';
        $this->migrationsDir = ROOT_PATH . '/database/migrations';

        // Command configuration
        $this->addArgument('name', 'The name of the model to create', true)
             ->addOption('migration', 'm', 'Create a migration file for the model')
             ->addOption('fields', 'f', 'Database fields in format name:type,name:type', true)
             ->addOption('table', 't', 'Table name (defaults to plural of model name)', true)
             ->addOption('force', null, 'Overwrite existing model file');
    }

    public function handle(array $arguments, array $options): int
    {
        try {
            $modelName = $this->normalizeModelName($arguments['name']);
            $createMigration = $options['migration'] ?? false;
            $force = $options['force'] ?? false;

            // Validate paths
            $this->validatePaths();

            // Check if model exists
            $modelPath = $this->getModelPath($modelName);
            if (file_exists($modelPath) && !$force) {
                throw new Exception("Model {$modelName} already exists. Use --force to overwrite.");
            }

            // Parse fields
            $fields = $this->getFields($options);

            // Determine table name
            $tableName = $this->getTableName($options, $modelName);

            // Create model file
            $this->generateModel($modelName, $tableName, $fields, $modelPath);

            // Create migration if requested
            if ($createMigration) {
                $this->generateMigration($modelName, $tableName, $fields);
            }

            TermUI::success("Model created successfully!");
            TermUI::info("Path: {$modelPath}");

            return 0;
        } catch (Exception $e) {
            TermUI::error($e->getMessage());
            return 1;
        }
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

        if (!is_dir($this->modelsDir)) {
            mkdir($this->modelsDir, 0755, true);
            TermUI::info("Created models directory: {$this->modelsDir}");
        }

        if (!is_dir($this->migrationsDir)) {
            mkdir($this->migrationsDir, 0755, true);
            TermUI::info("Created migrations directory: {$this->migrationsDir}");
        }
    }

    protected function getModelPath(string $modelName): string
    {
        return $this->modelsDir . '/' . $modelName . '.php';
    }

    protected function getFields(array $options): array
    {
        if (isset($options['fields']) && !empty($options['fields'])) {
            return $this->parseFields($options['fields']);
        }
        return $this->promptForFields();
    }

    protected function parseFields(string $fieldsString): array
    {
        $fields = [];
        $pairs = explode(',', $fieldsString);

        foreach ($pairs as $pair) {
            $parts = explode(':', trim($pair));
            if (count($parts) >= 2) {
                $fields[$parts[0]] = $parts[1];
            }
        }

        return $fields;
    }

    protected function promptForFields(): array
    {
        $fields = [];
        TermUI::info("Let's add fields to your model. Enter a blank field name to finish.");

        while (true) {
            $fieldName = TermUI::prompt("Field name (or leave blank to finish)");
            if (empty($fieldName)) {
                break;
            }

            $fieldType = TermUI::select("Field type", [
                'string' => 'String (VARCHAR)',
                'integer' => 'Integer',
                'text' => 'Text (LONGTEXT)',
                'boolean' => 'Boolean',
                'date' => 'Date',
                'datetime' => 'DateTime',
                'decimal' => 'Decimal'
            ]);

            $fields[$fieldName] = $fieldType;
        }

        return $fields;
    }

    protected function getTableName(array $options, string $modelName): string
    {
        if (isset($options['table']) && !empty($options['table'])) {
            return $options['table'];
        }
        return $this->pluralize(strtolower($modelName));
    }

    protected function generateModel(
        string $modelName,
        string $tableName,
        array $fields,
        string $modelPath
    ): void {
        $template = $this->getModelTemplate();

        // Generate fields definition
        $fieldsDefinition = '';
        foreach ($fields as $field => $type) {
            $fieldsDefinition .= "        '{$field}',\n";
        }

        $replacements = [
            '{{ModelName}}' => $modelName,
            '{{TableName}}' => $tableName,
            '{{Fields}}' => $fieldsDefinition,
            '{{Namespace}}' => 'App\\models',
            '{{Date}}' => date('Y-m-d'),
            '{{Year}}' => date('Y'),
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        file_put_contents($modelPath, $content);
    }

    protected function getModelTemplate(): string
    {
        $templatePath = $this->templatesDir . '/model.php.template';

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        return $this->getDefaultModelTemplate();
    }

    protected function generateMigration(
        string $modelName,
        string $tableName,
        array $fields
    ): void {
        $template = $this->getMigrationTemplate();

        // Generate migration filename
        $timestamp = date('Y_m_d_His');
        $migrationName = "Create{$modelName}Table";
        $migrationFileName = "{$timestamp}_{$migrationName}.php";

        // Generate fields for migration
        $fieldsDefinition = '';
        foreach ($fields as $field => $type) {
            $fieldsDefinition .= "            \$table->{$type}('{$field}');\n";
        }

        $replacements = [
            '{{MigrationName}}' => $migrationName,
            '{{TableName}}' => $tableName,
            '{{Fields}}' => $fieldsDefinition,
            '{{Namespace}}' => 'Database\\migrations',
            '{{Date}}' => date('Y-m-d'),
            '{{Year}}' => date('Y'),
        ];

        $content = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        $migrationPath = $this->migrationsDir . '/' . $migrationFileName;
        file_put_contents($migrationPath, $content);

        TermUI::info("Created migration: {$migrationPath}");
    }

    protected function getMigrationTemplate(): string
    {
        $templatePath = $this->templatesDir . '/migration.php.template';

        if (file_exists($templatePath)) {
            return file_get_contents($templatePath);
        }

        return $this->getDefaultMigrationTemplate();
    }

    protected function getDefaultModelTemplate(): string
    {
        return <<<'EOT'
<?php

declare(strict_types=1);

namespace {{Namespace}};

use Trees\Database\Model;

class {{ModelName}} extends Model
{
    /**
     * The table associated with the model.
     */
    protected string $table = '{{TableName}}';

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [
{{Fields}}
    ];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected array $hidden = [];
}
EOT;
    }

    protected function getDefaultMigrationTemplate(): string
    {
        return <<<'EOT'
<?php

declare(strict_types=1);

namespace {{Namespace}};

use Trees\Database\Migration\Migration;
use Trees\Database\Schema\Schema;

class {{MigrationName}} extends Migration
{
    public function up(): void
    {
        Schema::create('{{TableName}}', function ($table) {
            $table->id();
{{Fields}}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{{TableName}}');
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
}