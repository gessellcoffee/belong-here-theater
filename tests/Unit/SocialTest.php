<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Social;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_belong_to_a_user()
    {
        $user = User::factory()->create();

        $social = Social::create([
            'name' => 'Twitter',
            'url' => 'https://twitter.com/username',
            'icon' => 'twitter',
            'user_id' => $user->id,
            'entity_type' => 'user',
        ]);

        $this->assertTrue($social->isUserSocial());
        $this->assertFalse($social->isCompanySocial());
        $this->assertEquals($user->id, $social->user->id);
        $this->assertNull($social->company_id);
    }

    /** @test */
    public function it_can_belong_to_a_company()
    {
        $company = Company::factory()->create();

        $social = Social::create([
            'name' => 'Facebook',
            'url' => 'https://facebook.com/companypage',
            'icon' => 'facebook',
            'company_id' => $company->id,
            'entity_type' => 'company',
        ]);

        $this->assertTrue($social->isCompanySocial());
        $this->assertFalse($social->isUserSocial());
        $this->assertEquals($company->id, $social->company->id);
        $this->assertNull($social->user_id);
    }

    /** @test */
    public function it_requires_entity_type_to_match_the_provided_id()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        // Test with user_id but company entity_type
        $this->expectException(\Illuminate\Database\QueryException::class);
        Social::create([
            'name' => 'LinkedIn',
            'url' => 'https://linkedin.com/in/username',
            'icon' => 'linkedin',
            'user_id' => $user->id,
            'entity_type' => 'company', // Mismatch with user_id
        ]);

        // Test with company_id but user entity_type
        $this->expectException(\Illuminate\Database\QueryException::class);
        Social::create([
            'name' => 'Instagram',
            'url' => 'https://instagram.com/companypage',
            'icon' => 'instagram',
            'company_id' => $company->id,
            'entity_type' => 'user', // Mismatch with company_id
        ]);
    }

    /** @test */
    public function it_requires_either_user_id_or_company_id()
    {
        // Test without any ID
        $this->expectException(\Illuminate\Database\QueryException::class);
        Social::create([
            'name' => 'YouTube',
            'url' => 'https://youtube.com/channel',
            'icon' => 'youtube',
            'entity_type' => 'user', // No user_id provided
        ]);
    }
}
