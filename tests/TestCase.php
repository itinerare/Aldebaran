<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
    use CreatesApplication;

    protected function setUp(): void {
        parent::setUp();

        // Create a temporary user
        // As this is inherently a one-user project, this should suffice
        // as no user ID is ever recorded.
        $this->user = User::factory()->make();
    }

    /**
     * Returns all possible combinations of a given number of bools.
     * From https://stackoverflow.com/questions/29996895/list-all-combination-possible-in-a-php-array-of-boolean-value-in-php.
     *
     * @param int $length
     *
     * @return array
     */
    public function booleanSequences($length) {
        $totalCombos = pow(2, $length);

        $sequences = [];
        for ($x = 0; $x < $totalCombos; $x++) {
            $sequences[$x] = str_split(str_pad(decbin($x), $length, 0, STR_PAD_LEFT));
        }

        return $sequences;
    }
}
