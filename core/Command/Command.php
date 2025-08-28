<?php

declare(strict_types=1);

namespace Trees\Command;

use Exception;

abstract class Command
{
    protected string $name;
    protected string $description;
    protected array $arguments = [];
    protected array $options = [];
    protected bool $interactive = true;

    public function __construct()
    {
        $this->configure();
    }

    /**
     * Configure the command - should be implemented by child classes
     */
    abstract protected function configure(): void;

    /**
     * Get the command name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the command description
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Register an argument
     */
    protected function addArgument(
        string $name,
        string $description = '',
        bool $isRequired = false,
        $default = null
    ): self {
        $this->arguments[$name] = [
            'description' => $description,
            'required' => $isRequired,
            'default' => $default
        ];
        return $this;
    }

    /**
     * Register an option
     */
    protected function addOption(
        string $name,
        ?string $shortcut = null,
        string $description = '',
        bool $requiresValue = false,
        $default = null
    ): self {
        $this->options[$name] = [
            'shortcut' => $shortcut,
            'description' => $description,
            'requires_value' => $requiresValue,
            'default' => $default
        ];
        return $this;
    }

    /**
     * Parse command line arguments
     * @throws Exception
     */
    protected function parseInput(array $args): array
    {
        $parsed = [
            'command' => $this->name,
            'arguments' => [],
            'options' => []
        ];

        // Set default values
        foreach ($this->arguments as $name => $argument) {
            $parsed['arguments'][$name] = $argument['default'];
        }

        foreach ($this->options as $name => $option) {
            $parsed['options'][$name] = $option['default'];
        }

        $positionalArgs = [];
        $waitingForOptionValue = false;
        $currentOption = null;

        foreach ($args as $arg) {
            if ($waitingForOptionValue) {
                $parsed['options'][$currentOption] = $arg;
                $waitingForOptionValue = false;
                continue;
            }

            // Long option (--option)
            if (str_starts_with($arg, '--')) {
                $option = substr($arg, 2);
                $parts = explode('=', $option, 2);

                if (count($parts) === 2) {
                    [$option, $value] = $parts;
                    if (isset($this->options[$option])) {
                        $parsed['options'][$option] = $value;
                    }
                } else {
                    if (isset($this->options[$option])) {
                        if ($this->options[$option]['requires_value']) {
                            $waitingForOptionValue = true;
                            $currentOption = $option;
                        } else {
                            $parsed['options'][$option] = true;
                        }
                    }
                }
            }
            // Short option (-o)
            elseif (str_starts_with($arg, '-')) {
                $shortcut = substr($arg, 1);
                $found = false;

                foreach ($this->options as $name => $option) {
                    if ($option['shortcut'] === $shortcut) {
                        $found = true;
                        if ($option['requires_value']) {
                            $waitingForOptionValue = true;
                            $currentOption = $name;
                        } else {
                            $parsed['options'][$name] = true;
                        }
                        break;
                    }
                }

                if (!$found) {
                    throw new Exception("Unknown option: -{$shortcut}");
                }
            }
            // Positional argument
            else {
                $positionalArgs[] = $arg;
            }
        }

        // Assign positional arguments
        $argNames = array_keys($this->arguments);
        foreach ($positionalArgs as $index => $value) {
            if (isset($argNames[$index])) {
                $parsed['arguments'][$argNames[$index]] = $value;
            } else {
                throw new Exception("Too many arguments provided");
            }
        }

        // Validate required arguments
        foreach ($this->arguments as $name => $argument) {
            if ($argument['required'] && $parsed['arguments'][$name] === null) {
                if ($this->interactive) {
                    $parsed['arguments'][$name] = $this->promptForArgument($name, $argument);
                } else {
                    throw new Exception("Required argument '{$name}' is missing");
                }
            }
        }

        return $parsed;
    }

    /**
     * Show help information for this command
     */
    public function showHelp(): void
    {
        $content = [];
        $content[] = self::style("Description:", 'bold') . " " . $this->description;
        $content[] = "";

        if (!empty($this->arguments)) {
            $content[] = self::style("Arguments:", 'underline');
            $maxArgLength = max(array_map('strlen', array_keys($this->arguments)));

            foreach ($this->arguments as $name => $argument) {
                $required = $argument['required'] ? self::style(' (required)', 'red') : '';
                $default = $argument['default'] !== null ?
                    self::style(" [default: {$argument['default']}]", 'dim') : '';

                $paddedName = str_pad($name, $maxArgLength);
                $content[] = "  " . self::style($paddedName, 'green') .
                              "  " . $argument['description'] . $required . $default;
            }
            $content[] = "";
        }

        if (!empty($this->options)) {
            $content[] = self::style("Options:", 'underline');
            $maxOptLength = max(array_map(function($name) {
                return strlen($name) + (isset($this->options[$name]['shortcut']) ? 4 : 0);
            }, array_keys($this->options)));

            foreach ($this->options as $name => $option) {
                $shortcut = $option['shortcut'] ? self::style(" -{$option['shortcut']}", 'cyan') : '';
                $needsValue = $option['requires_value'] ? self::style(" <value>", 'yellow') : '';
                $default = $option['default'] !== null ?
                    self::style(" [default: {$option['default']}]", 'dim') : '';

                $content[] = "  " . self::style("--{$name}", 'green') . $shortcut . $needsValue .
                             "  " . $option['description'] . $default;
            }
        }

        TermUI::box(
            "COMMAND: " . self::style($this->name, 'bold'),
            implode(PHP_EOL, $content),
            TermUI::CYAN,
            TermUI::BG_CYAN . TermUI::BLACK
        );
    }

    /**
     * Prompt for missing argument values interactively
     */
    protected function promptForArgument(string $name, array $argument)
    {
        return TermUI::prompt(
            self::style("Enter value for argument '{$name}'", 'yellow'),
            $argument['default'],
            TermUI::YELLOW
        );
    }

    /**
     * Execute the command
     */
    abstract public function handle(array $arguments, array $options): int;

    /**
     * Run the command with the given arguments
     */
    public function run(array $args): int
    {
        $this->interactive = empty($args) || in_array('--interactive', $args);

        try {
            if (in_array('--help', $args) || in_array('-h', $args)) {
                $this->showHelp();
                return 0;
            }

            $input = $this->parseInput($args);
            return $this->handle($input['arguments'], $input['options']);
        } catch (Exception $e) {
            TermUI::error($e->getMessage());
            $this->showHelp();
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