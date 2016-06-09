<?php

namespace App\Http\Controllers;

use App\Poem;
use App\User;
use Illuminate\Http\Request;
use Response;
use App\Http\Requests;

class PoemsController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth');
    }
    
    public function index(Request $request){


        $search_term = $request->input('search');
        $limit = $request->input('limit')?$request->input('limit') : 5;

        if($search_term){

            $poems = Poem::orderBy('id','DESC')->where('body','LIKE',"%$search_term%")->with([
                'User'=>function($query){
                    $query->select('id','name');
                }
            ])->select('id','body','user_id')->paginate($limit);

            $poems->appends([
                'limit'     =>  $limit,
                'search'    =>  $search_term
            ]);

        }else{

            $poems = Poem::with([
                'User'=>function($query){
                    $query->select('id','name');
                }
            ])->select('id','body','user_id')->paginate($limit);

            $poems->appends([
                'limit'  =>  $limit
            ]);

        }


    	return Response::json([
            'data'   => $this->transformCollection($poems)
        ],200);

    }

    public function show($id)
    {

        $poem = Poem::with(
            array('User'=>function($query){
                $query->select('id','name');
            })
        )->find($id);

        if(!$poem){
            return Response::json([
                'error'=>[
                    'message' => 'Poem does not exist'
                ]
            ],404);
        }

        $previous = Poem::where('id','<',$poem->id)->max('id');

        $next = Poem::where('id','>',$poem->id)->min('id');

        return Response::json([
            'data'              =>  $this->transform($poem),
            'next_joke_id'      =>  $next,
            'previous_joke_id'  =>  $previous
        ],200);

    }

    public function store(Request $request)
    {

        if(! $request->body || !$request->user_id){
            return Response::json([
                'error' => [
                    'message'=>'Please provide both body and user id.'
                ]
            ],422);
        }

        $poem = Poem::create($request->all());

        return Response::json([
            'message'   =>  'Poem created successfully',
            'data'      =>  $this->transform($poem)
        ]);
    }

    public function update(Request $request, $id)
    {
        if(! $request->body || !$request->user_id){
            return Response::json([
                'error' => [
                    'message'=>'Please provide both body and user id.'
                ]
            ],422);
        }

        $poem           =   Poem::find($id);
        $poem->body     =   $request->body;
        $poem->user_id  =   $request->user_id;
        $poem->save();

        return Response::json([
            'message'   =>  'Poem updated successfully'
        ]);

    }

    public function destroy($id)
    {
        Poem::destroy($id);
    }

    private function transformCollection($poems)
    {

        $poemsArray = $poems->toArray();

        return [
            'total' =>  $poemsArray['total'],
            'per_page' =>  intval($poemsArray['per_page']),
            'current_page' =>  $poemsArray['current_page'],
            'last_page' =>  $poemsArray['last_page'],
            'next_page_url' =>  $poemsArray['next_page_url'],
            'prev_page_url' =>  $poemsArray['prev_page_url'],
            'from' =>  $poemsArray['from'],
            'to' =>  $poemsArray['to'],
            'data'  =>  array_map([$this,'transform'],$poemsArray['data'])
        ];

    }

    private function transform($poem)
    {

        return [
            'poem_id'           =>  $poem['id'],
            'poem'              =>  $poem['body'],
            'submitted_by'      =>  $poem['user']['name']
        ];
    }
}
