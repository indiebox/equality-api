<?php

namespace Tests\Feature;

use Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_image_helper()
    {
        $this->assertNull(image(null));
        $this->assertSame($url = 'https://equality.indiebox.ru/', image($url));
        $this->assertSame(asset('storage/' . $path = 'projects/test.jpg'), image($path));
    }
}
