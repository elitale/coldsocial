<?php

namespace App\Models;

use Database\Factories\PersonaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'primary_goal', 'headline', 'industry', 'experience_level', 'company', 'location',
    'personality_archetype', 'emoji_usage', 'formality', 'political_stance', 'political_leaning',
    'controversy_comfort', 'primary_platform', 'posting_frequency', 'audience_note', 'dislikes',
    'bio', 'languages', 'audiences', 'tones', 'interests', 'content_pillars', 'likes', 'causes',
    'content_formats', 'focus_platforms', 'social_links', 'completed_at',
])]
class Persona extends Model
{
    /** @use HasFactory<PersonaFactory> */
    use HasFactory;

    /**
     * Selectable options (value => label) per dimension — the single source of truth
     * shared by the onboarding form UI and server-side validation.
     *
     * @return array<string, array<string, string>>
     */
    public static function options(): array
    {
        return [
            'primary_goal' => [
                'entrepreneur' => 'Entrepreneur / founder',
                'tech_thought_leader' => 'Tech thought leader',
                'creator' => 'Creator / influencer',
                'executive' => 'Business executive',
                'coach' => 'Coach / consultant',
                'career' => 'Personal brand for my career',
                'community' => 'Community builder',
                'sales' => 'Sell products / services',
                'other' => 'Something else',
            ],
            'experience_level' => [
                'student' => 'Student',
                'junior' => 'Early career',
                'mid' => 'Mid-level',
                'senior' => 'Senior',
                'leader' => 'Leader / manager',
                'founder' => 'Founder / owner',
            ],
            'industry' => [
                'technology' => 'Technology',
                'saas' => 'SaaS / software',
                'ecommerce' => 'E-commerce',
                'finance' => 'Finance',
                'marketing' => 'Marketing / advertising',
                'healthcare' => 'Healthcare',
                'education' => 'Education',
                'media' => 'Media / entertainment',
                'consulting' => 'Consulting',
                'real_estate' => 'Real estate',
                'nonprofit' => 'Non-profit',
                'other' => 'Other',
            ],
            'languages' => [
                'english' => 'English', 'spanish' => 'Spanish', 'french' => 'French',
                'german' => 'German', 'hindi' => 'Hindi', 'portuguese' => 'Portuguese',
                'arabic' => 'Arabic', 'chinese' => 'Chinese',
            ],
            'audiences' => [
                'founders' => 'Founders', 'developers' => 'Developers', 'marketers' => 'Marketers',
                'designers' => 'Designers', 'investors' => 'Investors', 'executives' => 'Executives',
                'students' => 'Students', 'creators' => 'Creators', 'small_business' => 'Small business owners',
                'general_public' => 'General public',
            ],
            'tones' => [
                'professional' => 'Professional', 'casual' => 'Casual', 'witty' => 'Witty',
                'inspirational' => 'Inspirational', 'bold' => 'Bold / opinionated', 'friendly' => 'Friendly',
                'authoritative' => 'Authoritative', 'empathetic' => 'Empathetic',
            ],
            'personality_archetype' => [
                'expert' => 'The Expert', 'storyteller' => 'The Storyteller', 'motivator' => 'The Motivator',
                'contrarian' => 'The Contrarian', 'educator' => 'The Educator', 'entertainer' => 'The Entertainer',
            ],
            'emoji_usage' => ['none' => 'None', 'minimal' => 'Minimal', 'lots' => 'Lots'],
            'formality' => ['casual' => 'Casual', 'balanced' => 'Balanced', 'formal' => 'Formal'],
            'interests' => [
                'ai' => 'AI', 'startups' => 'Startups', 'saas' => 'SaaS', 'marketing' => 'Marketing',
                'design' => 'Design', 'finance' => 'Finance', 'crypto' => 'Crypto', 'productivity' => 'Productivity',
                'leadership' => 'Leadership', 'health' => 'Health', 'fitness' => 'Fitness', 'travel' => 'Travel',
                'food' => 'Food', 'sports' => 'Sports', 'gaming' => 'Gaming', 'music' => 'Music', 'art' => 'Art',
                'science' => 'Science', 'education' => 'Education', 'sustainability' => 'Sustainability',
            ],
            'likes' => [
                'how_to' => 'How-to guides', 'storytelling' => 'Storytelling', 'hot_takes' => 'Hot takes',
                'data_insights' => 'Data & insights', 'behind_the_scenes' => 'Behind the scenes',
                'listicles' => 'Listicles', 'case_studies' => 'Case studies', 'questions' => 'Questions & polls',
            ],
            'political_stance' => [
                'apolitical' => 'Keep it apolitical', 'occasional' => 'Occasionally', 'openly' => 'Openly political',
            ],
            'political_leaning' => [
                'prefer_not_to_say' => 'Prefer not to say', 'progressive' => 'Progressive',
                'centrist' => 'Centrist', 'conservative' => 'Conservative', 'other' => 'Other',
            ],
            'controversy_comfort' => [
                'avoid' => 'Avoid controversy', 'cautious' => 'Cautious', 'open' => 'Open to it',
            ],
            'causes' => [
                'sustainability' => 'Sustainability', 'diversity' => 'Diversity & inclusion',
                'open_source' => 'Open source', 'mental_health' => 'Mental health', 'education' => 'Education',
                'community' => 'Community', 'innovation' => 'Innovation', 'transparency' => 'Transparency',
            ],
            'content_formats' => [
                'short_text' => 'Short text posts', 'long_form' => 'Long-form', 'carousels' => 'Carousels',
                'video' => 'Video', 'images' => 'Images',
            ],
            'posting_frequency' => [
                'daily' => 'Daily', 'few_times_week' => 'A few times a week', 'weekly' => 'Weekly',
            ],
            'platforms' => [
                'linkedin' => 'LinkedIn', 'x' => 'X (Twitter)', 'instagram' => 'Instagram',
                'tiktok' => 'TikTok', 'youtube' => 'YouTube', 'facebook' => 'Facebook', 'threads' => 'Threads',
            ],
            'social_platforms' => [
                'linkedin' => 'LinkedIn', 'x' => 'X (Twitter)', 'instagram' => 'Instagram',
                'tiktok' => 'TikTok', 'youtube' => 'YouTube', 'facebook' => 'Facebook',
                'threads' => 'Threads', 'github' => 'GitHub', 'website' => 'Website',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'languages' => 'array',
            'audiences' => 'array',
            'tones' => 'array',
            'interests' => 'array',
            'content_pillars' => 'array',
            'likes' => 'array',
            'causes' => 'array',
            'content_formats' => 'array',
            'focus_platforms' => 'array',
            'social_links' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
