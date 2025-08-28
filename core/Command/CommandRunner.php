<?php

declare(strict_types=1);

namespace Trees\Command;

use Trees\Command\Command;

class CommandRunner
{
    protected string $name;
    protected string $version;
    protected array $commands = [];
    protected bool $interactive = true;

    public function __construct(string $name = 'CLI Tool', string $version = '1.0.0')
    {
        $this->name = $name;
        $this->version = $version;
        $this->interactive = $this->determineInteractiveMode();
    }

    protected function determineInteractiveMode(): bool
    {
        // More reliable interactive mode detection
        return php_sapi_name() === 'cli' &&
               empty($_SERVER['argv'][1]);
    }

    /**
     * Register a command
     */
    public function register(Command $command): self
    {
        $this->commands[$command->getName()] = $command;
        return $this;
    }

    /**
     * Auto-discover commands in a directory
     */
    public function discover(string $path, string $namespace): self
    {
        if (!is_dir($path)) {
            TermUI::warning("Command directory not found: {$path}");
            return $this;
        }

        foreach (glob($path . '/*.php') as $file) {
            $className = $namespace . '\\' . basename($file, '.php');

            if (!class_exists($className)) {
                continue;
            }

            if (is_subclass_of($className, Command::class)) {
                try {
                    $this->register(new $className());
                } catch (\Throwable $e) {
                    TermUI::error("Failed to register command {$className}: " . $e->getMessage());
                }
            }
        }

        return $this;
    }

    /**
     * Show list of available commands
     */
    public function showAvailableCommands(): void
    {
        $content = [];
        $content[] = self::style($this->name, 'bold') . " " . self::style("v{$this->version}", 'dim');
        $content[] = "";
        $content[] = self::style("Available commands:", 'underline');
        $content[] = "";

        $maxNameLength = max(array_map('strlen', array_keys($this->commands)));

        foreach ($this->commands as $name => $command) {
            $paddedName = str_pad($name, $maxNameLength);
            $content[] = "  " .
                         self::style($paddedName, 'green,bold') .
                         "  " . $command->getDescription();
        }

        $content[] = "";
        $content[] = self::style("Run", 'dim') . " " .
                     self::style("[command] --help", 'cyan') . " " .
                     self::style("for more information about a command.", 'dim');

        TermUI::box("HELP", implode(PHP_EOL, $content), TermUI::BLUE, TermUI::BG_BLUE . TermUI::WHITE);
    }

    /**
     * Let the user select a command interactively
     */
    // public function selectCommand(): ?string
    // {
    //     if (empty($this->commands)) {
    //         TermUI::error("No commands available to select");
    //         return null;
    //     }

    //     $options = [];
    //     foreach ($this->commands as $name => $command) {
    //         $options[$name] = self::style($name, 'green,bold') . ": " . $command->getDescription();
    //     }

    //     $selected = TermUI::select("Select a command to execute", $options);

    //     if ($selected === null) {
    //         TermUI::error("Invalid selection");
    //         return null;
    //     }

    //     return $selected;
    // }

    public function selectCommand(): ?array
    {
        if (empty($this->commands)) {
            TermUI::error("No commands available to select");
            return null;
        }

        $options = [];
        foreach ($this->commands as $name => $command) {
            $options[$name] = self::style($name, 'green,bold') . ": " . $command->getDescription();
        }

        $selectedCommand = TermUI::select("Select a command to execute", $options);

        if ($selectedCommand === null) {
            TermUI::error("No command selected");
            return null;
        }

        // Get arguments for the selected command
        $args = $this->promptForCommandArguments($selectedCommand);

        return [
            'command' => $selectedCommand,
            'arguments' => $args
        ];
    }

    /**
     * Prompt user for command arguments if needed
     */
    protected function promptForCommandArguments(string $commandName): array
    {
        $command = $this->commands[$commandName];
        $args = [];

        if (method_exists($command, 'getExpectedArguments')) {
            $expectedArgs = $command->getExpectedArguments();

            foreach ($expectedArgs as $argName => $config) {
                $prompt = $config['description'] ?? "Enter {$argName}";
                $default = $config['default'] ?? null;

                $value = TermUI::input($prompt, $default);
                if ($value !== null) {
                    $args[$argName] = $value;
                }
            }
        }

        return $args;
    }

    /**
     * Run the CLI tool
     */
    public function run(): int
    {
        $args = $_SERVER['argv'];
        array_shift($args); // Remove script name

        // Show help if no arguments or help flag
        if (empty($args) || in_array($args[0], ['list', '--help', '-h'])) {
            $this->showAvailableCommands();

            if ($this->interactive && empty($args)) {
                $selection = $this->selectCommand();
                if ($selection === null) {
                    return 1;
                }

                return $this->executeCommand($selection['command'], $selection['arguments']);
            }

            return 0;
        }

        $commandName = array_shift($args);

        if (!isset($this->commands[$commandName])) {
            TermUI::error("Command not found: " . $commandName);
            TermUI::info("Run with --help to see available commands.");
            return 1;
        }

        return $this->executeCommand($commandName, $args);
    }

    /**
     * Execute a command with proper error handling
     */
    protected function executeCommand(string $commandName, array $args): int
    {
        try {
            return $this->commands[$commandName]->run($args);
        } catch (\Throwable $e) {
            TermUI::error("Error executing command '{$commandName}':");
            TermUI::box("ERROR DETAILS", $e->getMessage(), TermUI::RED, TermUI::BG_RED . TermUI::WHITE);
            return 1;
        }
    }

    /**
     * Helper for styling text
     */
    protected static function style(string $text, string $styles): string
    {
        $styleMap = [
            'reset' => TermUI::RESET,
            'bold' => TermUI::BOLD,
            'dim' => TermUI::DIM,
            'italic' => TermUI::ITALIC,
            'underline' => TermUI::UNDERLINE,
            'black' => TermUI::BLACK,
            'red' => TermUI::RED,
            'green' => TermUI::GREEN,
            'yellow' => TermUI::YELLOW,
            'blue' => TermUI::BLUE,
            'magenta' => TermUI::MAGENTA,
            'cyan' => TermUI::CYAN,
            'white' => TermUI::WHITE,
        ];

        $result = '';
        foreach (explode(',', $styles) as $style) {
            $result .= $styleMap[$style] ?? '';
        }

        return $result . $text . TermUI::RESET;
    }
}