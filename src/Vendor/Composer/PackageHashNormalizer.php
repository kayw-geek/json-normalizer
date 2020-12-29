<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/json-normalizer
 */

namespace Ergebnis\Json\Normalizer\Vendor\Composer;

use Ergebnis\Json\Normalizer\Json;
use Ergebnis\Json\Normalizer\NormalizerInterface;

final class PackageHashNormalizer implements NormalizerInterface
{
    /**
     * @see https://github.com/composer/composer/blob/1.6.2/src/Composer/Repository/PlatformRepository.php#L27
     */
    private const PLATFORM_PACKAGE_REGEX = '{^(?:php(?:-64bit|-ipv6|-zts|-debug)?|hhvm|(?:ext|lib)-[^/ ]+)$}i';

    /**
     * @phpstan-var list<string>
     * @psalm-var list<string>
     *
     * @var array<int, string>
     */
    private static $properties = [
        'conflict',
        'provide',
        'replace',
        'require',
        'require-dev',
        'suggest',
    ];

    public function normalize(Json $json): Json
    {
        $decoded = $json->decoded();

        if (!\is_object($decoded)) {
            return $json;
        }

        $objectProperties = \array_intersect_key(
            \get_object_vars($decoded),
            \array_flip(self::$properties)
        );

        if (0 === \count($objectProperties)) {
            return $json;
        }

        foreach ($objectProperties as $name => $value) {
            /** @var array<string, string> $packages */
            $packages = (array) $decoded->{$name};

            if (0 === \count($packages)) {
                continue;
            }

            $decoded->{$name} = self::sortPackages($packages);
        }

        /** @var string $encoded */
        $encoded = \json_encode($decoded);

        return Json::fromEncoded($encoded);
    }

    /**
     * This code is adopted from composer/composer (originally licensed under MIT by Nils Adermann <naderman@naderman.de>
     * and Jordi Boggiano <j.boggiano@seld.be>).
     *
     * @see https://github.com/composer/composer/blob/1.6.2/src/Composer/Json/JsonManipulator.php#L110-L146
     *
     * @param array<string, string> $packages
     *
     * @return array<string, string>
     */
    private static function sortPackages(array $packages): array
    {
        $prefix = static function (string $requirement): string {
            if (1 === \preg_match(self::PLATFORM_PACKAGE_REGEX, $requirement)) {
                return \preg_replace(
                    [
                        '/^php/',
                        '/^hhvm/',
                        '/^ext/',
                        '/^lib/',
                        '/^\D/',
                    ],
                    [
                        '0-$0',
                        '1-$0',
                        '2-$0',
                        '3-$0',
                        '4-$0',
                    ],
                    $requirement
                );
            }

            return '5-' . $requirement;
        };

        \uksort($packages, static function (string $a, string $b) use ($prefix): int {
            return \strnatcmp($prefix($a), $prefix($b));
        });

        return $packages;
    }
}
