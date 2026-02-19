import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\VersionController::restore
 * @see app/Http/Controllers/Admin/VersionController.php:18
 * @route '/editor/{model}/restore/{version_id}'
 */
export const restore = (args: { model: string | number, version_id: string | number } | [model: string | number, version_id: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})

restore.definition = {
    methods: ["post"],
    url: '/editor/{model}/restore/{version_id}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\VersionController::restore
 * @see app/Http/Controllers/Admin/VersionController.php:18
 * @route '/editor/{model}/restore/{version_id}'
 */
restore.url = (args: { model: string | number, version_id: string | number } | [model: string | number, version_id: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    model: args[0],
                    version_id: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        model: args.model,
                                version_id: args.version_id,
                }

    return restore.definition.url
            .replace('{model}', parsedArgs.model.toString())
            .replace('{version_id}', parsedArgs.version_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\VersionController::restore
 * @see app/Http/Controllers/Admin/VersionController.php:18
 * @route '/editor/{model}/restore/{version_id}'
 */
restore.post = (args: { model: string | number, version_id: string | number } | [model: string | number, version_id: string | number ], options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: restore.url(args, options),
    method: 'post',
})
const post = {
    restore: Object.assign(restore, restore),
}

export default post