import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
import post from './post'
/**
* @see \App\Http\Controllers\Admin\UsersController::deleteMethod
 * @see app/Http/Controllers/Admin/UsersController.php:70
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
 * @see app/Http/Controllers/Admin/UsersController.php:70
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
 * @see app/Http/Controllers/Admin/UsersController.php:70
 * @route '/admin/users/{id}'
 */
deleteMethod.delete = (args: { id: string | number } | [id: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteMethod.url(args, options),
    method: 'delete',
})
const user = {
    post: Object.assign(post, post),
delete: Object.assign(deleteMethod, deleteMethod),
}

export default user