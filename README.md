# Medici Core
Medici core package

- Repository, Pipeline
- NodesTree, EloquentNestedSet
- ApiResponser
- StatusCode, Error Handler
- File Helpers.php: upload_images, resize_image, upload_private_images, get_url_private, medici_logger

## Helpers

Thêm HelperServiceProvider vào config/app.php, example:
```injectablephp
    'providers' => [
        ...
        App\Providers\HelperServiceProvider::class,
    ],
```
hoặc thêm vào trong 
```injectablephp
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        foreach (glob('medicivn/core/Helpers/*.php') as $file) {
            require_once $file;
        }
    }
```

## EloquentNestedSet

Automatically update the tree when creating, updating and deleting a node.

How to use:
- First, a root node must be initialized in your model's table
- Add ```use EloquentNestedSet;``` to your eloquent model, example:

```injectablephp
class Category extends Model
{
    use EloquentNestedSet;

    const ROOT_ID   = 99999; // the root node id, default: 1
    const LEFT      = 'lft'; // the left position column name of a node, default: 'lft'
    const RIGHT     = 'rgt'; // the right position column name of a node, default: 'rgt'
    const PARENT_ID = 'parent_id'; // the parent's id column name of a node, default: 'parent_id'
```

Note: the values of the left and right columns accept negative values which need some processing logic

### Validation

NestedSet ParentRule is a utility to validate parent_id value when creating and updating a node.

It will be checked with the following condition:
- exists in the database
- is not the same as the id of the current entity
- not a descendant of the current entity

Example:

```injectablephp
class StoreCategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'parent_id' => [
                new NestedSetParentRule(Category::class, $this->id)
            ]
        ];
    }
}
```
