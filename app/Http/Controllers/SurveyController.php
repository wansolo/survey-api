<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Response;
use mysql_xdevapi\Exception;


class SurveyController extends Controller
{

    /**
     * List available Survey items.
     *
     * @response array{data: Survey[]}
     */
    public function index()
    {
        return response()->json(Survey::with('questions')->paginate(10));
    }


    /**
     * Create new Survey item.
     *
     * @response array{data: Question[]}
     */
    public function store(Request $request)
    {

        $request->validate([
            'title' => ['required', 'string','unique:surveys,title'],// Ensure title is unique
            'questions' => ['required', 'array', 'min:1'], // Ensure questions is an array and has at least one item
            'questions.*.question_text' => ['required', 'string'], // Validate each question's text
            'questions.*.question_type' => ['required', 'string'], // Validate each question's type
            'questions.*.options' => ['nullable', 'array'], // Validate options if present
        ]);
        $survey = Survey::create($request->only('title'));

        foreach ($request->questions as $question) {
            $survey->questions()->create([
                'survey_id'=>$survey->id,
                'question'=>$question["question_text"],
                'type'=>$question["question_type"],
                'options'=> isset($question['options'])?$question["options"]:null,

                ]);
        }

        return response()->json($survey->load('questions'), 201);
    }

    /**
     * Show Single Survey item.
     *
     * @response array{data: Question[]}
     */
    public function show(Survey $survey):JsonResponse
    {
        try {

            $surveyItem = $survey->load('questions');
            return response()->json($surveyItem);
        }catch (\Exception $e){
            return response()->json($e->getMessage());
             }

    }

    /**
     * Store Survey Responses.
     * @param Response
     *
     */
    public function storeResponses(Request $request, Survey $survey)
    {
        $request->validate([
            'responses' => 'required|array',
            'responses.*.question_id' => 'required|exists:questions,id',
            'responses.*.response' => 'required|string',
        ]);
        foreach ($request->responses as $response) {
            Response::create([
                'survey_id' => $survey->id,
                'question_id' => $response['question_id'],
                'response' => $response['response'],
            ]);
        }

        return response()->json(['message' => 'Responses submitted successfully'], 201);
    }


    /**
     * Show Results for Survey item.
     *
     * @response array{data: Response[]}
     */
    public function showResults(Survey $survey)
    {
        $surveyResults = $survey->load('questions.responses');

        return response()->json($surveyResults);
    }
}
