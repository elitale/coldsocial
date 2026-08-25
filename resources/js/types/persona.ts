export type PersonaOptions = Record<string, Record<string, string>>;

export interface Persona {
    primary_goal: string | null;
    headline: string | null;
    industry: string | null;
    experience_level: string | null;
    company: string | null;
    location: string | null;
    personality_archetype: string | null;
    emoji_usage: string | null;
    formality: string | null;
    political_stance: string | null;
    political_leaning: string | null;
    controversy_comfort: string | null;
    primary_platform: string | null;
    posting_frequency: string | null;
    audience_note: string | null;
    dislikes: string | null;
    bio: string | null;
    languages: string[] | null;
    audiences: string[] | null;
    tones: string[] | null;
    interests: string[] | null;
    content_pillars: string[] | null;
    likes: string[] | null;
    causes: string[] | null;
    content_formats: string[] | null;
    focus_platforms: string[] | null;
    social_links: Record<string, string> | null;
    completed_at: string | null;
}
