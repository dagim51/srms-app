<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIAssistant
{
    protected string $provider;

    protected array $config;

    public function __construct()
    {
        $this->provider = config('ai.provider', 'openai');
        $this->config = config('ai.providers.'.$this->provider, []);
    }

    /**
     * Generate property description from features
     */
    public function generatePropertyDescription(array $features, array $options = []): string
    {
        $prompt = $this->buildDescriptionPrompt($features, $options);

        return $this->complete($prompt, [
            'temperature' => 0.7,
            'max_tokens' => 500,
            'system' => 'You are a professional real estate copywriter for the Ethiopian market. Write compelling, accurate property descriptions in English. Include local context (Addis Ababa neighborhoods, Ethiopian amenities). Tone: professional, inviting, factual.',
        ]);
    }

    /**
     * Generate listing title
     */
    public function generateTitle(array $features): string
    {
        $prompt = "Create a compelling real estate listing title (max 80 chars) for:\n";
        $prompt .= "- Type: {$features['type']}\n";
        $prompt .= "- Bedrooms: {$features['bedrooms']}\n";
        $prompt .= "- Location: {$features['location']}\n";
        $prompt .= '- Key features: '.implode(', ', $features['highlights'] ?? [])."\n";
        $prompt .= 'Style: Professional, specific, includes location and key selling point.';

        return $this->complete($prompt, [
            'temperature' => 0.5,
            'max_tokens' => 60,
            'system' => 'Real estate title generator. Output ONLY the title, no quotes.',
        ]);
    }

    /**
     * Improve/rewrite existing description
     */
    public function improveDescription(string $current, array $context = []): string
    {
        $prompt = "Improve this property description for an Ethiopian real estate listing:\n\n";
        $prompt .= $current."\n\n";
        $prompt .= 'Context: '.json_encode($context)."\n\n";
        $prompt .= 'Requirements: Fix grammar, enhance flow, add local context (Ethiopia/Addis), highlight unique features, keep factual, professional tone, 200-400 words.';

        return $this->complete($prompt, [
            'temperature' => 0.4,
            'max_tokens' => 600,
            'system' => 'Professional real estate copywriter. Output ONLY the improved description.',
        ]);
    }

    /**
     * Generate marketing copy for social media
     */
    public function generateMarketingCopy(array $property, string $platform = 'instagram'): array
    {
        $platformGuidance = match ($platform) {
            'instagram' => 'Visual-first, hashtags, emoji-friendly, engaging hook, call-to-action',
            'facebook' => 'Story-driven, community-focused, detailed, shareable',
            'linkedin' => 'Professional, investment-focused, data-driven, networking tone',
            'telegram' => 'Concise, direct, contact info prominent, Amharic/Oromo optional',
            default => 'General purpose marketing copy',
        };

        $prompt = "Create {$platform} marketing copy for:\n";
        $prompt .= "- Title: {$property['title']}\n";
        $prompt .= "- Price: {$property['price']}\n";
        $prompt .= "- Location: {$property['location']}\n";
        $prompt .= '- Features: '.implode(', ', $property['features'] ?? [])."\n";
        $prompt .= "- Style: {$platformGuidance}\n";
        $prompt .= 'Include relevant hashtags for Ethiopian real estate.';

        $copy = $this->complete($prompt, [
            'temperature' => 0.6,
            'max_tokens' => 400,
            'system' => 'Social media marketing expert for Ethiopian real estate. Output: headline, body, hashtags, CTA.',
        ]);

        return $this->parseMarketingOutput($copy);
    }

    /**
     * Answer user questions about property/process (chat assistant)
     */
    public function chat(array $messages, array $context = []): string
    {
        $systemPrompt = $this->buildSystemPrompt($context);

        return $this->complete($messages, [
            'temperature' => 0.3,
            'max_tokens' => 800,
            'system' => $systemPrompt,
        ]);
    }

    /**
     * Extract structured data from free-text property description
     */
    public function extractPropertyData(string $description): array
    {
        $prompt = "Extract structured property data from this description. Return ONLY valid JSON:\n\n";
        $prompt .= $description."\n\n";
        $prompt .= 'Fields: title, type, bedrooms, bathrooms, area_sqm, price_etb, location, features (array), condition, furnished (boolean), parking (boolean), balcony (boolean). Use null for unknown.';

        $response = $this->complete($prompt, [
            'temperature' => 0.1,
            'max_tokens' => 400,
            'system' => 'Data extraction specialist. Output ONLY valid JSON object.',
        ]);

        return $this->safeJsonDecode($response, []);
    }

    /**
     * Analyze market question and provide data-driven answer
     */
    public function analyzeMarketQuestion(string $question, array $marketData = []): string
    {
        $prompt = "Answer this real estate market question for Ethiopia/Addis Ababa using the provided data:\n\n";
        $prompt .= "Question: {$question}\n\n";
        $prompt .= "Market Data:\n".json_encode($marketData, JSON_PRETTY_PRINT)."\n\n";
        $prompt .= 'Provide specific numbers, trends, and actionable insights. Cite data sources. Be concise but comprehensive.';

        return $this->complete($prompt, [
            'temperature' => 0.2,
            'max_tokens' => 600,
            'system' => 'Real estate market analyst for Ethiopia. Data-driven, specific, professional.',
        ]);
    }

    /**
     * Generate contract clause suggestions
     */
    public function suggestContractClauses(array $dealParams): array
    {
        $prompt = "Suggest key contract clauses for this Ethiopian real estate deal:\n";
        $prompt .= json_encode($dealParams, JSON_PRETTY_PRINT)."\n\n";
        $prompt .= 'Consider: Ethiopian Civil Code, lease law, registration requirements, dispute resolution, force majeure, payment terms, default remedies. Output as bullet points with clause name and purpose.';

        return $this->complete($prompt, [
            'temperature' => 0.3,
            'max_tokens' => 500,
            'system' => 'Ethiopian real estate legal expert. Practical, compliant with local law.',
        ]);
    }

    /**
     * Build description generation prompt
     */
    private function buildDescriptionPrompt(array $features, array $options): string
    {
        $prompt = "Write a compelling property description for an Ethiopian real estate listing:\n\n";

        foreach ($features as $key => $value) {
            if (is_array($value)) {
                $prompt .= "- {$key}: ".implode(', ', $value)."\n";
            } else {
                $prompt .= "- {$key}: {$value}\n";
            }
        }

        $prompt .= "\nTarget audience: ".($options['audience'] ?? 'middle to upper income buyers/renters in Ethiopia')."\n";
        $prompt .= 'Length: '.($options['length'] ?? '200-300 words')."\n";
        $prompt .= "Language: English (with local context)\n";
        $prompt .= "Include: neighborhood context, lifestyle benefits, investment potential, call to action.\n";
        $prompt .= "Avoid: generic phrases, exaggeration, vague superlatives.\n";

        return $prompt;
    }

    /**
     * Build system prompt for chat
     */
    private function buildSystemPrompt(array $context): string
    {
        $base = "You are an AI assistant for SRMS - Ethiopia's premier real estate platform. ";
        $base .= 'You help users with property search, valuation, legal process, market analysis, and platform features. ';
        $base .= 'You have access to: property listings, market trends, valuation models, Ethiopian real estate law, financing options. ';
        $base .= 'Guidelines: ';
        $base .= '1. Be specific to Ethiopian market (Addis Ababa, regional cities). ';
        $base .= '2. Cite data sources when giving numbers. ';
        $base .= '3. For legal/financial advice, include disclaimer to consult professionals. ';
        $base .= '4. Be concise but comprehensive. ';
        $base .= '5. Support English, Amharic, Afaan Oromo. ';

        if (! empty($context)) {
            $base .= "\nCurrent context: ".json_encode($context);
        }

        return $base;
    }

    /**
     * Core completion method with provider abstraction
     */
    private function complete(mixed $prompt, array $options = []): string
    {
        $provider = $this->provider;

        return match ($provider) {
            'openai' => $this->completeOpenAI($prompt, $options),
            'anthropic' => $this->completeAnthropic($prompt, $options),
            'local' => $this->completeLocal($prompt, $options),
            default => $this->fallbackResponse($prompt, $options),
        };
    }

    private function completeOpenAI(mixed $prompt, array $options): string
    {
        $apiKey = $this->config['api_key'] ?? env('OPENAI_API_KEY');

        if (! $apiKey) {
            return $this->fallbackResponse($prompt, $options);
        }

        $messages = [];

        if (isset($options['system'])) {
            $messages[] = ['role' => 'system', 'content' => $options['system']];
        }

        if (is_string($prompt)) {
            $messages[] = ['role' => 'user', 'content' => $prompt];
        } elseif (is_array($prompt)) {
            $messages = array_merge($messages, $prompt);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(30)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->config['model'] ?? 'gpt-4o-mini',
                    'messages' => $messages,
                    'temperature' => $options['temperature'] ?? 0.7,
                    'max_tokens' => $options['max_tokens'] ?? 1000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? $this->fallbackResponse($prompt, $options);
            }

            Log::warning('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);

        } catch (\Exception $e) {
            Log::error('OpenAI completion failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackResponse($prompt, $options);
    }

    private function completeAnthropic(mixed $prompt, array $options): string
    {
        $apiKey = $this->config['api_key'] ?? env('ANTHROPIC_API_KEY');

        if (! $apiKey) {
            return $this->fallbackResponse($prompt, $options);
        }

        // Similar structure for Anthropic
        return $this->fallbackResponse($prompt, $options);
    }

    private function completeLocal(mixed $prompt, array $options): string
    {
        // Local LLM (Ollama, etc.) - placeholder
        return $this->fallbackResponse($prompt, $options);
    }

    /**
     * Fallback when no AI provider configured
     */
    private function fallbackResponse(mixed $prompt, array $options): string
    {
        if (is_string($prompt) && str_contains($prompt, 'property description')) {
            return $this->templateDescription($prompt);
        }

        if (is_string($prompt) && str_contains($prompt, 'title')) {
            return 'Premium Property in Prime Location';
        }

        if (is_array($prompt)) {
            return "I'm here to help with your real estate questions. For detailed market analysis, valuation, or legal guidance, please consult with our verified agents or legal professionals.";
        }

        return 'AI service temporarily unavailable. Please try again or contact support.';
    }

    private function templateDescription(string $prompt): string
    {
        // Extract key features from prompt using simple parsing
        $features = [];
        preg_match_all('/- (\w+): (.+)/', $prompt, $matches);
        if (isset($matches[1], $matches[2])) {
            $features = array_combine($matches[1], $matches[2]);
        }

        $type = $features['type'] ?? 'property';
        $bedrooms = $features['bedrooms'] ?? 'spacious';
        $location = $features['location'] ?? 'prime area';
        $highlights = $features['highlights'] ?? 'modern amenities';

        return "Discover this exceptional {$type} featuring {$bedrooms} bedrooms in {$location}. ".
               "This property offers {$highlights} in one of Ethiopia's most sought-after neighborhoods. ".
               'With thoughtful design, quality finishes, and convenient access to schools, shopping, and transport, '.
               'it presents an ideal blend of comfort and investment potential. '.
               'Schedule your private viewing today to experience the lifestyle this home offers.';
    }

    private function parseMarketingOutput(string $output): array
    {
        return [
            'headline' => '',
            'body' => $output,
            'hashtags' => '#AddisAbabaRealEstate #EthiopiaProperties #SRMS #RealEstateEthiopia',
            'cta' => 'Contact us for a viewing!',
        ];
    }

    private function safeJsonDecode(string $json, array $default = []): array
    {
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : $default;
    }
}
