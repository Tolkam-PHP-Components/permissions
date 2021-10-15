# tolkam/permissions

Role based permissions management.

## Documentation

The code is rather self-explanatory and API is intended to be as simple as possible. Please, read the sources/Docblock if you have any questions. See [Usage](#usage) for quick start.

## Usage

````php

use Tolkam\Permissions\Grant\Grant;
use Tolkam\Permissions\PermissionsManager;
use Tolkam\Permissions\Resource\CRUDResource;
use Tolkam\Permissions\Role\Role;

$permissionsManager = new PermissionsManager;

$reader = new Role('reader');
$editor = new Role('editor', 'reader');
$owner = new Role('owner', 'editor');

$resource = new CRUDResource('article');
$allResourceActions = $resource->getActions();

$permissionsManager->addResources($resource);
$permissionsManager->addRoles($reader, $editor, $owner);
$permissionsManager->addGrants(
    new Grant('reader', 'article', ['read']),
    new Grant('editor', 'article', ['update']),
    new Grant('owner', 'article', $allResourceActions)
);

$permissionsManager->compile();

foreach ([$reader, $editor, $owner] as $role) {
    foreach ($resource->getActions() as $action) {
        
        $roleName = $role->getName();
        $resourceName = $resource->getName();
        
        if ($permissionsManager->can($roleName, $action, $resourceName)) {
            echo "$roleName can $action $resourceName\n";
        }
        else {
            echo "$roleName can`t $action $resourceName\n";
        }
    }
    echo PHP_EOL;
}
````

## License

Proprietary / Unlicensed 🤷
