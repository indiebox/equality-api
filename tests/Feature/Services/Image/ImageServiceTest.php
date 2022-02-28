<?php

namespace Tests\Feature\Services\Image;

use App\Services\Contracts\Image\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    /**
     * @var ImageService
     */
    protected $image;

    protected function setUp(): void
    {
        parent::setUp();

        $this->image = app(ImageService::class);
    }

    public function test_save()
    {
        Storage::fake();

        $path = $this->image->save(UploadedFile::fake()->image('test.jpg'), 'test/dir');

        Storage::assertExists($path);
    }

    public function test_save_with_name()
    {
        Storage::fake();

        $path = $this->image->save(UploadedFile::fake()->image('test.jpg'), 'test/dir', 'name.jpg');

        Storage::assertExists($path);
        $this->assertTrue(Str::endsWith($path, 'name.jpg'));
    }

    public function test_delete()
    {
        Storage::fake();

        $path = $this->image->save(UploadedFile::fake()->image('test.jpg'), 'test/dir');

        Storage::assertExists($path);

        $result = $this->image->delete($path);

        $this->assertTrue($result);
        Storage::assertMissing($path);
    }

    public function test_delete_not_existing_path_returns_false()
    {
        Storage::fake();

        $result = $this->image->delete('some/path');
        $this->assertFalse($result);

        $result = $this->image->delete(null);
        $this->assertFalse($result);
    }
}
