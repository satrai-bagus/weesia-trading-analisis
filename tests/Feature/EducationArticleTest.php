<?php

namespace Tests\Feature;

use App\Models\EducationArticle;
use App\Models\EducationCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_publish_step_based_education_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->post(route('admin.education.store'), [
                'title' => 'Cara Membaca Chart Untuk Pemula',
                'category' => EducationArticle::CATEGORY_CHART,
                'summary' => 'Belajar membaca area entry, TP, dan SL.',
                'is_published' => '1',
                'steps' => [
                    [
                        'title' => 'Kenali area entry',
                        'body' => 'Mulai dari area entry dan tunggu konfirmasi candle.',
                    ],
                    [
                        'title' => 'Ukur risk reward',
                        'body' => 'Bandingkan jarak stop loss dan target profit.',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.education'));

        $this->assertDatabaseHas('education_articles', [
            'title' => 'Cara Membaca Chart Untuk Pemula',
            'category' => EducationArticle::CATEGORY_CHART,
        ]);

        $this->assertDatabaseHas('education_steps', [
            'title' => 'Kenali area entry',
            'sort_order' => 1,
        ]);

        $article = EducationArticle::firstOrFail();

        $this->actingAs($user)
            ->get(route('education.show', $article))
            ->assertOk()
            ->assertSee('Cara Membaca Chart Untuk Pemula')
            ->assertSee('Kenali area entry');
    }

    public function test_draft_education_article_is_hidden_from_regular_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($admin)
            ->post(route('admin.education.store'), [
                'title' => 'Draft Exchange Flow',
                'category' => EducationArticle::CATEGORY_EXCHANGE,
                'summary' => 'Belum siap dipublish.',
                'steps' => [
                    [
                        'title' => 'Pilih market',
                        'body' => 'Cari pair yang ingin dibeli.',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.education'));

        $article = EducationArticle::firstOrFail();

        $this->actingAs($user)
            ->get(route('education.index'))
            ->assertOk()
            ->assertDontSee('Draft Exchange Flow');

        $this->actingAs($user)
            ->get(route('education.show', $article))
            ->assertNotFound();
    }

    public function test_admin_can_manage_education_categories(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.education-categories.store'), [
                'name' => 'Risk Management',
            ])
            ->assertRedirect();

        $category = EducationCategory::where('slug', 'risk-management')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('admin.education-categories.update', $category), [
                'name' => 'Risk Management Dasar',
                'sort_order' => 9,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('education_categories', [
            'slug' => 'risk-management',
            'name' => 'Risk Management Dasar',
            'sort_order' => 9,
            'is_active' => true,
        ]);
    }
}
