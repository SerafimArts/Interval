<?php
/**
 * This file is part of Interval package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

use Serafim\Interval\Range;

if (! \function_exists('interval')) {
    /**
     * @param int|float $from
     * @param int|float $to
     * @return iterable|Range
     */
    function interval($from, $to = \INF): iterable
    {
        return new Range($from, $to);
    }
}
