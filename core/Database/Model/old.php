<?php

declare(strict_types=1);

namespace Trees\Database\Model;

/**
 * =======================================
 * ***************************************
 * ======== Trees Model Class ============
 * ***************************************
 * =======================================
 */

use Trees\Database\Database;
use Trees\Database\QueryBuilder\QueryBuilder;
use Trees\Validation\Validator;
use Trees\Database\Trait\Pagination;
use Trees\Database\Relationships\Traits\HasRelationships;

abstract class old
{
    use Pagination, HasRelationships;

    /**
     * @var string The table name associated with the model
     */
    protected string $table = '';

    /**
     * @var string The primary key column name
     */
    protected string $primaryKey = 'id';

    /**
     * @var array Fillable attributes that can be mass-assigned
     */
    protected array $fillable = [];

    /**
     * @var array Hidden attributes that should be excluded from serialization
     */
    protected array $hidden = [];

    /**
     * @var array Validation rules for model attributes
     */
    protected array $rules = [];

    /**
     * @var array The current model attributes
     */
    protected array $attributes = [];

    /**
     * @var array Original attributes before any modifications
     */
    protected array $original = [];

    /**
     * @var array Changed attributes
     */
    protected array $changes = [];

    /**
     * @var bool Whether the model exists in the database
     */
    protected bool $exists = false;

    /**
     * @var bool Whether to enforce fillable restriction (only for mass assignment)
     */
    protected bool $enforceFillable = true;

    /**
     * Constructor
     *
     * @param array $attributes Initial model attributes
     * @param bool $enforceFillable Whether to enforce fillable restriction
     */
    public function __construct(array $attributes = [], bool $enforceFillable = true)
    {
        // Set the table name if not already set
        if (empty($this->table)) {
            $this->table = strtolower(
                preg_replace(
                    '/(?<!^)[A-Z]/',
                    '_$0',
                    (new \ReflectionClass($this))->getShortName() . 's'
                )
            );
        }

        $this->enforceFillable = $enforceFillable;

        // Fill the model with attributes
        if (!empty($attributes)) {
            $this->fill($attributes);
        }
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Fill the model with an array of attributes
     *
     * @param array $attributes Attributes to fill
     * @param bool $enforceFillable Whether to enforce fillable restriction
     * @return self
     */
    public function fill(array $attributes, bool $enforceFillable = null): self
    {
        $enforceFillable = $enforceFillable ?? $this->enforceFillable;

        foreach ($attributes as $key => $value) {
            if (!$enforceFillable || empty($this->fillable) || in_array($key, $this->fillable)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Set an attribute
     *
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     * @return self
     */
    public function setAttribute(mixed $key, mixed $value): self
    {
        // Track changes only if the value is different from original
        if (array_key_exists($key, $this->original) && $this->original[$key] !== $value) {
            $this->changes[$key] = $value;
        } elseif (!array_key_exists($key, $this->original)) {
            $this->changes[$key] = $value;
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get an attribute
     *
     * @param string $key Attribute name
     * @return mixed Attribute value
     */
    public function getAttribute(string $key): mixed
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        // Check for relationships or other dynamic attributes
        if (method_exists($this, $key)) {
            return $this->getRelationshipValue($key);
        }

        return null;
    }

    /**
     * Get a relationship value
     *
     * @param string $key Relationship method name
     * @return mixed
     */
    protected function getRelationshipValue(string $key): mixed
    {
        $relation = $this->$key();

        // Add this check
        if (is_array($relation)) {
            return $relation;
        }

        if (method_exists($relation, 'getResults')) {
            return $relation->getResults();
        }

        return $relation;
    }

    /**
     * Magic method to get attributes
     *
     * @param string $key Attribute name
     * @return mixed Attribute value
     */
    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    /**
     * Magic method to set attributes
     *
     * @param string $key Attribute name
     * @param mixed $value Attribute value
     */
    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Validate the model attributes
     *
     * @return bool True if valid, false otherwise
     */
    public function validate(): bool
    {
        // If no validation rules, consider it valid
        if (empty($this->rules)) {
            return true;
        }

        // Use a validation class (you'd need to implement this)
        $validator = new Validator($this->attributes, $this->rules);
        return $validator->passes();
    }

    // Add to your Model class
    public function beginTransaction(): bool
    {
        return Database::getInstance()->beginTransaction();
    }

    public function commit(): bool
    {
        return Database::getInstance()->commit();
    }

    public function rollBack(): bool
    {
        return Database::getInstance()->rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }

    /**
     * Save the model to the database
     *
     * @return bool True if saved successfully, false otherwise
     */
    public function save(): bool
    {
        // Validate the model
        if (!$this->validate()) {
            return false;
        }

        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return false;
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        try {
            // Begin transaction
            $db->beginTransaction();

            if ($this->exists) {
                // Update existing record
                $result = $builder->table($this->table)
                    ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
                    ->update($this->changes);
            } else {
                // Insert new record
                $result = $builder->table($this->table)
                    ->insert($this->attributes);

                // Set the ID for the new record
                if ($result !== false) {
                    $newId = $db->lastInsertId();
                    $this->setAttribute($this->primaryKey, $newId);
                    $this->exists = true;
                }
            }

            // Commit transaction
            $db->commit();

            // Reset changes after successful save
            $this->original = $this->attributes;
            $this->changes = [];

            return $result !== false;
        } catch (\Exception $e) {
            // Rollback transaction
            $db->rollback();

            // Log the error
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }

            return false;
        }
    }

    /**
     * Begin a fluent query
     */
    public static function query(): QueryBuilder
    {
        $db = Database::getInstance();
        $model = new static();
        return (new QueryBuilder($db))->table($model->table);
    }

    /**
     * Static update method
     */
    public static function updateWhere(array $conditions, array $attributes): bool
    {
        $db = Database::getInstance();
        $model = new static();
        $builder = new QueryBuilder($db);

        $query = $builder->table($model->table);
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        return $query->update($attributes) !== false;
    }

    /**
     * Update the model in the database
     *
     * @param array $attributes Attributes to update
     * @return bool True if update was successful, false otherwise
     */
    public function update(array $attributes): bool
    {
        // Don't update if model doesn't exist
        if (!$this->exists) {
            return false;
        }

        // Fill the model with new attributes
        $this->fill($attributes);

        // Validate the model
        if (!$this->validate()) {
            return false;
        }

        // Get database connection
        $db = Database::getInstance();
        if (!$db) {
            return false;
        }

        // Create query builder
        $builder = new QueryBuilder($db);

        try {
            // Begin transaction
            $db->beginTransaction();

            // Update only changed attributes
            $result = $builder->table($this->table)
                ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
                ->update($this->changes);

            // Commit transaction
            $db->commit();

            if ($result !== false) {
                // Update original attributes to reflect changes
                $this->original = array_merge($this->original, $this->changes);
                $this->changes = [];
                return true;
            }

            return false;
        } catch (\Exception $e) {
            // Rollback transaction
            $db->rollback();

            // Log error
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }

            return false;
        }
    }

    /**
     * Instance method for update
     */
    public function updateInstance(array $attributes): bool
    {
        if (!$this->exists) {
            return false;
        }

        $this->fill($attributes);
        return $this->save();
    }

    /**
     * Delete the model from the database
     *
     * @return bool True if deleted successfully, false otherwise
     */
    public function delete(): bool
    {
        // Ensure the model exists in the database
        if (!$this->exists) {
            return false;
        }

        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return false;
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        try {
            // Begin transaction
            $db->beginTransaction();

            // Delete the record
            $result = $builder->table($this->table)
                ->where($this->primaryKey, $this->getAttribute($this->primaryKey))
                ->delete();

            // Commit transaction
            $db->commit();

            // Reset model state if deleted successfully
            if ($result !== false) {
                $this->exists = false;
                $this->attributes = [];
                $this->changes = [];
                $this->original = [];
            }

            return $result !== false;
        } catch (\Exception $e) {
            // Rollback transaction
            $db->rollback();

            // Log the error
            if (class_exists('\Trees\Logger\Logger')) {
                \Trees\Logger\Logger::exception($e);
            }

            return false;
        }
    }

    /**
     * Find a model by its primary key
     *
     * @param mixed $id Primary key value
     * @return static|null The found model or null
     */
    public static function find(mixed $id): ?self
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return null;
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Find the record
        $result = $builder->table($model->table)
            ->where($model->primaryKey, $id)
            ->first();

        // Return null if no record found
        if (!$result) {
            return null;
        }

        // Create a model instance with the found data
        $instance = new $className();
        $instance->fill($result);
        $instance->exists = true;
        $instance->original = $result;
        $instance->changes = [];

        return $instance;
    }

    /**
     * Find models matching given conditions
     *
     * @param array $conditions Conditions to filter by
     * @return array Array of model instances
     */
    public static function where(array $conditions): array
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return [];
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Apply conditions
        $query = $builder->table($model->table);
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        // Execute the query
        $results = $query->get();

        // Convert results to model instances
        if (!$results) {
            return [];
        }

        return array_map(function ($result) use ($className) {
            $instance = new $className($result);
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }, $results);
    }

    /**
     * Instance method for where conditions
     */
    public function whereInstance(string $column, mixed $value): self
    {
        $this->attributes[$column] = $value;
        return $this;
    }

    /**
     * Find models not matching given conditions
     *
     * @param array $conditions Conditions to filter by
     * @return array Array of model instances
     */
    public static function whereNot(array $conditions): array
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return [];
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Apply conditions
        $query = $builder->table($model->table);
        foreach ($conditions as $column => $value) {
            $query->whereNot($column, $value);
        }

        // Execute the query
        $results = $query->get();

        // Convert results to model instances
        if (!$results) {
            return [];
        }

        return array_map(function ($result) use ($className) {
            $instance = new $className($result);
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }, $results);
    }

    /**
     * Find models where column value is not in the given array
     *
     * @param string $column The column name
     * @param array $values The values to exclude
     * @return array Array of model instances
     */
    public static function whereNotIn(string $column, array $values): array
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return [];
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Apply the NOT IN condition
        $results = $builder->table($model->table)
            ->whereNotIn($column, $values)
            ->get();

        // Convert results to model instances
        if (!$results) {
            return [];
        }

        return array_map(function ($result) use ($className) {
            $instance = new $className($result);
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }, $results);
    }

    /**
     * Find models where column value is in the given array
     *
     * @param string $column The column name
     * @param array $values The values to include
     * @return array Array of model instances
     */
    public static function whereIn(string $column, array $values): array
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return [];
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Apply the IN condition
        $results = $builder->table($model->table)
            ->whereIn($column, $values)
            ->get();

        // Convert results to model instances
        if (!$results) {
            return [];
        }

        return array_map(function ($result) use ($className) {
            $instance = new $className($result);
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }, $results);
    }

    /**
     * Get all records for the model
     *
     * @return array Array of model instances
     */
    public static function all(): array
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return [];
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Get all records
        $results = $builder->table($model->table)->get();

        // Convert results to model instances
        if (!$results) {
            return [];
        }

        return array_map(function ($result) use ($className) {
            $instance = new $className($result);
            $instance->exists = true;
            $instance->original = $result;
            return $instance;
        }, $results);
    }

    /**
     * Create a new model instance and save it
     *
     * @param array $attributes Attributes for the new model
     * @return static|null The created model or null on failure
     */
    public static function create(array $attributes): ?self
    {
        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className($attributes);

        // Save the model
        return $model->save() ? $model : null;
    }

    /**
     * Get the first model matching the conditions
     *
     * @param array $conditions Conditions to filter by
     * @return static|null The found model or null
     */
    public static function first(array $conditions = []): ?self
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return null;
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Apply conditions if provided
        $query = $builder->table($model->table);
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        // Get the first record
        $result = $query->first();

        if (!$result) {
            return null;
        }

        // Create a model instance with the found data
        $instance = new $className();
        $instance->fill($result);
        $instance->exists = true;
        $instance->original = $result;
        $instance->changes = [];

        return $instance;
    }

    /**
     * Count the number of records
     *
     * @param array $conditions Optional conditions to filter by
     * @return int The count of records
     * @throws \Exception
     */
    public static function count(array $conditions = []): int
    {
        // Get the database connection
        $db = Database::getInstance();
        if (!$db) {
            return 0;
        }

        // Create a query builder
        $builder = new QueryBuilder($db);

        // Get the called class
        $className = static::class;

        // Create a new instance
        $model = new $className();

        // Apply conditions if provided
        $query = $builder->table($model->table);
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }

        return $query->count();
    }

    /**
     * Check if a record exists
     *
     * @param array $conditions Conditions to filter by
     * @return bool True if exists, false otherwise
     * @throws \Exception
     */
    public static function exists(array $conditions): bool
    {
        return self::count($conditions) > 0;
    }

    /**
     * Apply search conditions to the query
     *
     * @param QueryBuilder $query The query builder instance
     * @param string $searchTerm The search term to apply
     */
    protected function applySearch(QueryBuilder $query, string $searchTerm): void
    {
        // Default implementation does nothing
        // Child classes should override this if they need search functionality
    }

    /**
     * Convert the model to an array
     *
     * @return array Model attributes as an array
     */
    public function toArray(): array
    {
        // Remove hidden attributes
        $attributes = $this->attributes;
        foreach ($this->hidden as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }

    /**
     * Convert the model to a JSON string
     *
     * @return string JSON representation of the model
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Magic method for JSON serialization
     *
     * @return array Serializable data
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Magic method to convert model to string
     *
     * @return string JSON representation
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
