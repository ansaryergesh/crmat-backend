<?php

namespace App\Http\Controllers;
use DB;
use Carbon\Carbon;
use App\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $fio = $request->input('fio');
        $email = $request->input('email');
        $iin = $request->input('iin');
        $phone = $request->input('phone');
        $service_id = $request->input('service_id');
        $organization_id = $request->input('organization_id');
        $result['success'] = false;
        $user = DB::table('guests')->where('iin', $iin)->first();
        
        DB::beginTransaction();
        do {
          if($user) {
            $uslugi_user = DB::table('guest_uslugis')->insert(
              array(
                'guest_id'=>$user->id,
                'service_id'=>$service_id,
                'organization_id'=>$organization_id,
              )
            );
            if(!$uslugi_user) {
              DB::rollback();
              $result['message'] = 'Что то пошло не так';
            }
        }
        else {
            $new_guest = DB::table('guests')->insertGetId(
                array(
                    'fio'=>$fio,
                    'email'=>$email,
                    'iin'=>$iin,
                    'phone'=>$phone,
                )
            );

            if(!$new_guest) {
                DB::rollback();
                $result['message'] = 'Что то пошло не так';
            }

            $uslugi_user = DB::table('guest_uslugis')->insert(
              array(
                'guest_id'=>$new_guest,
                'service_id'=>$service_id,
                'organization_id'=>$organization_id,
              )
            );

            if(!$uslugi_user) {
              DB::rollback();
              $result['message'] = 'Что то пошло не так';
            }
        }
          DB::commit();
          $result['success'] = true;
          $result['message'] = 'Добавлен и перенаправлен';
        }while(false);
        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Guest  $guest
     * @return \Illuminate\Http\Response
     */
    public function show(Guest $guest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Guest  $guest
     * @return \Illuminate\Http\Response
     */
    public function edit(Guest $guest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Guest  $guest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Guest $guest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Guest  $guest
     * @return \Illuminate\Http\Response
     */
    public function destroy(Guest $guest)
    {
        //
    }
}
