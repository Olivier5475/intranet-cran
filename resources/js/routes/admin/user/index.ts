import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
import post from './post'
/**
* @see \App\Http\Controllers\Admin\UsersController::create
 * @see app/Http/Controllers/Admin/UsersController.php:70
 * @route '/admin/users/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/users/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UsersController::create
 * @see app/Http/Controllers/Admin/UsersController.php:70
 * @route '/admin/users/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UsersController::create
 * @see app/Http/Controllers/Admin/UsersController.php:70
 * @route '/admin/users/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UsersController::create
 * @see app/Http/Controllers/Admin/UsersController.php:70
 * @route '/admin/users/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\UsersController::update
 * @see app/Http/Controllers/Admin/UsersController.php:73
 * @route '/admin/users/{id}'
 */
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})

update.definition = {
    methods: ["get","head"],
    url: '/admin/users/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\UsersController::update
 * @see app/Http/Controllers/Admin/UsersController.php:73
 * @route '/admin/users/{id}'
 */
update.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return update.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UsersController::update
 * @see app/Http/Controllers/Admin/UsersController.php:73
 * @route '/admin/users/{id}'
 */
update.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\UsersController::update
 * @see app/Http/Controllers/Admin/UsersController.php:73
 * @route '/admin/users/{id}'
 */
update.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: update.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\UsersController::deleteMethod
 * @see app/Http/Controllers/Admin/UsersController.php:87
 * @route '/admin/users/{id}'
 */
export const deleteMethod = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/admin/users/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\UsersController::deleteMethod
 * @see app/Http/Controllers/Admin/UsersController.php:87
 * @route '/admin/users/{id}'
 */
deleteMethod.url = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { id: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    id: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        id: args.id,
                }

    return deleteMethod.definition.url
            .replace('{id}', parsedArgs.id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UsersController::deleteMethod
 * @see app/Http/Controllers/Admin/UsersController.php:87
 * @route '/admin/users/{id}'
 */
deleteMethod.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})
const user = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
post: Object.assign(post, post),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default user