<?php

namespace App\Http\Controllers;

use App\Models\AllowedDomains;
use App\Models\Forms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FormsController extends Controller
{

    protected $formsModel;
    public function __construct(Forms $forms)
    {
        $this->formsModel = $forms;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $index = $this->formsModel->where('creator_id', $user->id)->get();

        return response()->json([ 'message' => 'Get all forms success', 'forms' => $index ], 200);
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
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:forms,slug|regex:/^[a-zA-Z0-9.-]+$/',
            'allowed_domains' => 'array'
        ], [
            'slug.regex' => 'The slug field must only contain letters, numbers, dashes, dot and without spacing'
        ]);

        if ($validation->fails()) 
            return response()->json([ 'message' => 'Invalid field', 'errors' => $validation->errors() ], 422);

        $user = auth()->user();

        $store = collect($request->only($this->formsModel->getFillable()))
        ->put('creator_id', $user->id)
        ->toArray();
        $new = $this->formsModel->create($store);

        foreach ($request->allowed_domains as $domain)
        {
            AllowedDomains::create([
                'form_id' => $new->id,
                'domain' => $domain
            ]);
        }

        return response()->json([ 'message' => 'Create form success', 'form' => $store ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show($form_slug)
    {
        $form = $this->formsModel->where('slug', $form_slug)
        ->with(['allowed_domains', 'questions'])
        ->first();
        
        if (!$form)
        {
            return response()->json([ 'message' => 'Form not found' ], 404);
        }
        
        $user = auth()->user();
        
        $user_domain = explode('@', $user->email)[1];
        $allowed_domains = $form->allowed_domains->pluck('domain')->toArray();

        if (!in_array($user_domain, $allowed_domains))
        {
            return response()->json([ 'message' => 'Forbidden access' ], 403);
        }

        return response()->json([ 'message' => 'Get form success', 'form' => $form ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Forms $forms)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Forms $forms)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Forms $forms)
    {
        //
    }
}
