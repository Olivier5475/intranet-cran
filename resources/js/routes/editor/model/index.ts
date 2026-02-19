import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
import post from './post'
/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:39
 * @route '/editor/{model}/history/{model_id}'
 */
export const history = (args: { model: string | number, model_id: string | number } | [model: string | number, model_id: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})

history.definition = {
    methods: ["get","head"],
    url: '/editor/{model}/history/{model_id}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:39
 * @route '/editor/{model}/history/{model_id}'
 */
history.url = (args: { model: string | number, model_id: string | number } | [model: string | number, model_id: string | number ], options?: RouteQueryOptions) => {
    if (Array.isArray(args)) {
        args = {
                    model: args[0],
                    model_id: args[1],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        model: args.model,
                                model_id: args.model_id,
                }

    return history.definition.url
            .replace('{model}', parsedArgs.model.toString())
            .replace('{model_id}', parsedArgs.model_id.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:39
 * @route '/editor/{model}/history/{model_id}'
 */
history.get = (args: { model: string | number, model_id: string | number } | [model: string | number, model_id: string | number ], options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: history.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\VersionController::history
 * @see app/Http/Controllers/Admin/VersionController.php:39
 * @route '/editor/{model}/history/{model_id}'
 */
history.head = (args: { model: string | number, model_id: string | number } | [model: string | number, model_id: string | number ], options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: history.url(args, options),
    method: 'head',
})
const model = {
    history: Object.assign(history, history),
post: Object.assign(post, post),
}

export default model