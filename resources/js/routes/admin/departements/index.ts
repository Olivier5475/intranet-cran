import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
import post from './post'
/**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:65
 * @route '/admin/departements/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/departements/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:65
 * @route '/admin/departements/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:65
 * @route '/admin/departements/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DepartementController::create
 * @see app/Http/Controllers/Admin/DepartementController.php:65
 * @route '/admin/departements/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:68
 * @route '/admin/departements/{id}'
 */
export const update = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})

update.definition = {
    methods: ["get","head"],
    url: '/admin/departements/{id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:68
 * @route '/admin/departements/{id}'
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
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:68
 * @route '/admin/departements/{id}'
 */
update.get = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: update.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\DepartementController::update
 * @see app/Http/Controllers/Admin/DepartementController.php:68
 * @route '/admin/departements/{id}'
 */
update.head = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: update.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\DepartementController::deleteMethod
 * @see app/Http/Controllers/Admin/DepartementController.php:82
 * @route '/admin/departements/{id}'
 */
export const deleteMethod = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})

deleteMethod.definition = {
    methods: ["delete"],
    url: '/admin/departements/{id}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\DepartementController::deleteMethod
 * @see app/Http/Controllers/Admin/DepartementController.php:82
 * @route '/admin/departements/{id}'
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
* @see \App\Http\Controllers\Admin\DepartementController::deleteMethod
 * @see app/Http/Controllers/Admin/DepartementController.php:82
 * @route '/admin/departements/{id}'
 */
deleteMethod.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})
const departements = {
    create: Object.assign(create, create),
update: Object.assign(update, update),
post: Object.assign(post, post),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default departements