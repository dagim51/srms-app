<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AI\AIAssistant;
use Illuminate\Http\Request;

class AIAssistantController extends Controller
{
    public function __construct(
        protected AIAssistant $assistant
    ) {}

    /**
     * Generate property description from features
     */
    public function generateDescription(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'area_sqm' => 'nullable|numeric|min:1',
            'location' => 'required|string',
            'price_etb' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'condition' => 'nullable|string',
            'furnished' => 'nullable|boolean',
            'highlights' => 'nullable|array',
            'highlights.*' => 'string',
        ]);

        $description = $this->assistant->generatePropertyDescription($validated);

        return response()->json([
            'data' => [
                'description' => $description,
            ],
        ]);
    }

    /**
     * Generate listing title
     */
    public function generateTitle(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'bedrooms' => 'nullable|integer|min:0',
            'location' => 'required|string',
            'highlights' => 'nullable|array',
            'highlights.*' => 'string',
        ]);

        $title = $this->assistant->generateTitle($validated);

        return response()->json([
            'data' => [
                'title' => $title,
            ],
        ]);
    }

    /**
     * Improve existing description
     */
    public function improveDescription(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'context' => 'nullable|array',
        ]);

        $improved = $this->assistant->improveDescription($validated['description'], $validated['context'] ?? []);

        return response()->json([
            'data' => [
                'description' => $improved,
            ],
        ]);
    }

    /**
     * Generate marketing copy for social media
     */
    public function marketingCopy(Request $request)
    {
        $validated = $request->validate([
            'property' => 'required|array',
            'property.title' => 'required|string',
            'property.price' => 'required|string',
            'property.location' => 'required|string',
            'property.features' => 'nullable|array',
            'platform' => 'nullable|string|in:instagram,facebook,linkedin,telegram',
        ]);

        $copy = $this->assistant->generateMarketingCopy($validated['property'], $validated['platform'] ?? 'instagram');

        return response()->json([
            'data' => $copy,
        ]);
    }

    /**
     * Chat with AI assistant
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'messages' => 'required|array',
            'messages.*.role' => 'required|in:user,assistant,system',
            'messages.*.content' => 'required|string',
            'context' => 'nullable|array',
        ]);

        $response = $this->assistant->chat($validated['messages'], $validated['context'] ?? []);

        return response()->json([
            'data' => [
                'response' => $response,
            ],
        ]);
    }

    /**
     * Extract structured property data from description
     */
    public function extractPropertyData(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        $data = $this->assistant->extractPropertyData($validated['description']);

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Analyze market question
     */
    public function analyzeMarket(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'market_data' => 'nullable|array',
        ]);

        $answer = $this->assistant->analyzeMarketQuestion($validated['question'], $validated['market_data'] ?? []);

        return response()->json([
            'data' => [
                'answer' => $answer,
            ],
        ]);
    }

    /**
     * Suggest contract clauses
     */
    public function contractClauses(Request $request)
    {
        $validated = $request->validate([
            'deal_params' => 'required|array',
        ]);

        $clauses = $this->assistant->suggestContractClauses($validated['deal_params']);

        return response()->json([
            'data' => [
                'clauses' => $clauses,
            ],
        ]);
    }
}
