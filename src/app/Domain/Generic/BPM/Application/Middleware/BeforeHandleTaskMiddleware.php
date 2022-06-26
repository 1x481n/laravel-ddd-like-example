<?php
/**
 * Created by IntelliJ IDEA.
 * User: 1x481n
 * Date: 2022/6/16
 * Time: 10:31 PM
 */

namespace App\Domain\Generic\BPM\Application\Middleware;


use Closure;
use Illuminate\Http\Request;

/**
 * @deprecated 暂时无用
 */
class BeforeHandleTaskMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

}
