<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\AcademicInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $students = Student::join('users','users.id','=','students.user_id')
            ->join('academic_infos','academic_infos.id','=','students.academic_info_id')
            ->get();
        return $students;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = new User;
        $user->firstName=$request->firstName;
        $user->lastName=$request->lastName;
        $user->email=$request->email;
        $user->email_verified_at= date('Y-m-d H:i:s');
        $user->password=Hash::make($request->password);
        $user->gender=$request->gender;
        $user->image= 'https://southernplasticsurgery.com.au/wp-content/uploads/2013/10/user-placeholder.png';
        $user->type='student';
        $user->phone=$request->phone;
        $user->save();

        $academicInfo = new AcademicInfo;
        $academicInfo->department = $request->department; 
        $academicInfo->school = $request->school;
        $academicInfo->save(); 

        $student = new Student;
        $student->user_id=$user->id;
        $student->studentCode=$request->studentCode;
        $student->gradeYear=$request->gradeYear;
        $student->academic_info_id= $academicInfo->id;
        $student->save();
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $student = Student::where(['id'=>$id])->first();
        $user_id = $student->user_id;
        
        //return Student::find($id)->user;
    
        return Student::join('users','users.id','=','students.user_id')
            ->where(['user_id' => $user_id])
            ->join('academic_infos','academic_infos.id','=','students.academic_info_id')
            ->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
