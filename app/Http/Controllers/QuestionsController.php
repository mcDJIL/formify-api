<?php

namespace App\Http\Controllers;

use App\Models\Forms;
use App\Models\Questions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QuestionsController extends Controller
{

    protected $questionsModel;
    public function __construct(Questions $questions)
    {
        $this->questionsModel = $questions;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'choice_type' => 'required|in:short answer,paragraph,date,multiple choice,dropdown,checkboxes',
            'choices' => 'required_if:choice_type,multiple choice,dropdown,checkboxes'
        ], [
            'choice_type.in' => 'The choice type field must be one of: short answer, paragraph, date, multiple choice, dropdown, checkboxes'
        ]);

        if ($validation->fails()) return response()->json([ 'message' => 'Invalid field', 'errors' => $validation->errors() ], 422);

        $form = Forms::where('slug', $form_slug)->first();

        if (!$form)
        { 
            return response()->json([ 'message' => 'Form not found' ], 404);
        }
        
        $user = auth()->user();
        if ($form->creator_id != $user->id)
        {
            return response()->json([ 'message' => 'Forbidden access' ], 403);
        }

        if ($request->choices != null) 
        {
            $choices = implode(', ', $request->choices);
        }
        else 
        {
            $choices = null;
        }

        $question = collect($request->only($this->questionsModel->getFillable()))
        ->put('form_id', $form->id)
        ->put('choices', $choices)
        ->toArray();

        $new = $this->questionsModel->create($question);

        return response()->json([ 'message' => 'Add question success', 'question' => $question ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Questions $questions)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Questions $questions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Questions $questions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($form_slug, $question_id)
    {
        $form = Forms::where('slug', $form_slug)->first();

        if (!$form)
        { 
            return response()->json([ 'message' => 'Form not found' ], 404);
        }

        $question = $this->questionsModel->where('id', $question_id)->first();
        
        if (!$question) 
        {
            return response()->json([ 'message' => 'Question not found' ], 404);
        }

        if ($question->form_id != $form->id)
        {
            return response()->json([ 'message' => 'Form not found' ], 404);
        }

        $user = auth()->user();
        if ($form->creator_id != $user->id)
        {
            return response()->json([ 'message' => 'Forbidden access' ], 403);
        }

        $delete = $this->questionsModel->where('id', $question_id)->delete();

        return response()->json([ 'message' => 'Remove question success' ]);
    }
}
