<?php

namespace App\Http\Controllers;

use App\Models\AllowedDomains;
use App\Models\Answers;
use App\Models\Forms;
use App\Models\Questions;
use App\Models\Responses;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ResponsesController extends Controller
{

    protected $answerModel;
    protected $responsesModel;
    public function __construct(Responses $responses, Answers $answers)
    {
        $this->responsesModel = $responses;

        $this->answerModel = $answers;
    }

    /**
     * Display a listing of the resource.
     */
    public function index($form_slug)
    {
        $form = Forms::where('slug', $form_slug)->first();
        $user = auth()->user();

        if (!$form)
        {
            return response()->json([ 'message' => 'Form not found' ], 404);
        }

        if ($form->creator_id != $user->id)
        {
            return response()->json([ 'message' => 'Forbidden access' ], 403);
        }

        $responses = $this->responsesModel->where('form_id', $form->id)->with('user')->get();

        $result = [];

        foreach ($responses as $response)
        {
            $answer = $this->answerModel->where('response_id', $response->id)
            ->with('question')
            ->get()
            ->pluck('value', 'question.name')
            ->toArray();

            $result[] = [
                'date' => $response->date,
                'user' => $response->user,
                'answers' => $answer
            ];
        }

        return response()->json([ 'message' => 'Get responses success', 'responses' => $result ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $form_slug)
    {
        $form = Forms::where('slug', $form_slug)->first();
        $user = auth()->user();

        $question = Questions::where('form_id', $form->id)->first();

        if ($question->is_required === 1) {
            $required = 'required';
        }
        else {
            $required = '';
        }

        $validation = Validator::make($request->all(), [
            'answers' => "array|$required"
        ], [
            'answers' => 'The answers field is required'
        ]);

        if ($validation->fails()) return response()->json([ 'message' => 'Invalid field', 'errors' => $validation->errors() ], 422);

        $user_domain = explode("@", $user->email)[1];
        $allowed_domains = AllowedDomains::where('form_id', $form->id)->pluck('domain')->toArray();

        if (!in_array($user_domain, $allowed_domains))
        {
            return response()->json([ 'message' => 'Forbidden access' ], 403);
        }

        $isResponse = $this->responsesModel->where('user_id', $user->id)->where('form_id', $form->id)->first();

        if ($isResponse && $form->limit_one_response === 1)
        {
            return response()->json([ 'message' => 'You can not submit form twice' ], 422);
        }

        $today = Carbon::now()->toDateTimeLocalString();

        $response = collect($request->only($this->responsesModel->getFillable()))
        ->put('form_id', $form->id)
        ->put('user_id', $user->id)
        ->put('date', $today)
        ->toArray();

        $newResponse = $this->responsesModel->create($response);

        foreach ($request->answers as $answers)
        {
            $response = $this->responsesModel->where('date', $today)->first();

            $this->answerModel->create([
                'response_id' => $response->id,
                'question_id' => $answers['question_id'],
                'value' => $answers['value'],
            ]);

            // $store = collect($request->only($this->answerModel->getFillable()))
            // ->put('response_id', $response->id)
            // ->put('question_id', $question_id)
            // ->put('value', $answer)
            // ->toArray();

            // $newAnswer = $this->answerModel->create($store);
        }

        return response()->json([ 'message' => "Submit response success" ], 200);

    }

    /**
     * Display the specified resource.
     */
    public function show(Responses $responses)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Responses $responses)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Responses $responses)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Responses $responses)
    {
        //
    }
}
