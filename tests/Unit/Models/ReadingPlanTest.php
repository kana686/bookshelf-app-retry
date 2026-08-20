<?php

namespace Tests\Unit\Models;

use App\Models\ReadingPlan;
use PHPUnit\Framework\TestCase;

class ReadingPlanTest extends TestCase
{
    public function test_fillableに設定された属性が一括代入できる()
    {
        $readingPlan = new ReadingPlan;

        $expected = [
            'user_id',
            'book_id',
            'target_date',
            'completed_at',
            'status',

        ];

        $this->assertEquals($expected, $readingPlan->getFillable());
    }
}
