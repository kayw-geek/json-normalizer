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

namespace Ergebnis\Json\Normalizer\Test\Unit\Exception;

use Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolvedException;

/**
 * @internal
 *
 * @covers \Ergebnis\Json\Normalizer\Exception\SchemaUriCouldNotBeResolvedException
 */
final class SchemaUriCouldNotBeResolvedExceptionTest extends AbstractExceptionTestCase
{
    public function testFromSchemaUriReturnsSchemaUriCouldNotBeResolvedException(): void
    {
        $schemaUri = self::faker()->url;

        $exception = SchemaUriCouldNotBeResolvedException::fromSchemaUri($schemaUri);

        $message = \sprintf(
            'Schema URI "%s" could not be resolved.',
            $schemaUri
        );

        self::assertSame($message, $exception->getMessage());
        self::assertSame($schemaUri, $exception->schemaUri());
    }
}
