<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_avatar_upload_creates_missing_profile_details(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/lt/profile/avatar')
            ->post('/lt/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('photo.jpg'),
            ]);

        $response->assertRedirect('/lt/profile/avatar')->assertSessionHasNoErrors();
        $path = $user->fresh()->details->avatar;
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_avatar_can_be_removed_without_changing_other_profile_data(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $details = $user->details;
        $details->avatar = 'avatars/old.jpg';
        $details->handle = 'my-profile';
        $details->description = 'Keep this description';
        $details->save();
        Storage::disk('public')->put('avatars/old.jpg', 'old avatar');

        $response = $this->actingAs($user)->from('/lt/profile/avatar')
            ->post('/lt/profile/avatar', ['remove_avatar' => '1']);

        $response->assertRedirect('/lt/profile/avatar')->assertSessionHasNoErrors();
        $details = $user->fresh()->details;
        $this->assertNull($details->avatar);
        $this->assertSame('my-profile', $details->handle);
        $this->assertSame('Keep this description', $details->description);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }

    public function test_replacing_an_avatar_removes_the_previous_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $details = $user->details;
        $details->avatar = 'avatars/old.jpg';
        $details->save();
        Storage::disk('public')->put('avatars/old.jpg', 'old avatar');

        $this->actingAs($user)->from('/lt/profile/avatar')
            ->post('/lt/profile/avatar', [
                'avatar' => UploadedFile::fake()->image('new.jpg'),
            ])->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing('avatars/old.jpg');
        Storage::disk('public')->assertExists($user->fresh()->details->avatar);
    }

    public function test_invalid_upload_keeps_the_previous_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $details = $user->details;
        $details->avatar = 'avatars/old.jpg';
        $details->save();
        Storage::disk('public')->put('avatars/old.jpg', 'old avatar');

        $this->actingAs($user)->from('/lt/profile/avatar')
            ->post('/lt/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain'),
            ])->assertSessionHasErrors('avatar');

        $this->assertSame('avatars/old.jpg', $user->fresh()->details->avatar);
        Storage::disk('public')->assertExists('avatars/old.jpg');
    }

    public function test_removal_does_not_affect_another_users_avatar(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $other = User::factory()->create();
        foreach ([$owner, $other] as $user) {
            $details = $user->details;
            $details->avatar = 'avatars/'.$user->id.'.jpg';
            $details->save();
            Storage::disk('public')->put($details->avatar, 'avatar');
        }

        $this->actingAs($owner)->post('/lt/profile/avatar', ['remove_avatar' => '1'])
            ->assertSessionHasNoErrors();

        $this->assertNull($owner->fresh()->details->avatar);
        $this->assertSame('avatars/'.$other->id.'.jpg', $other->fresh()->details->avatar);
        Storage::disk('public')->assertExists('avatars/'.$other->id.'.jpg');
    }
}
