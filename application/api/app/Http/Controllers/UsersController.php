<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;


class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
	 * Log user by token and ID provided through GET method
	 *
	 * @param void
	 * @return User
	 */
	function loginUserByToken(Request $request) {

		$user = \Contacts::instance()->findById($request->id);
		if(!($user instanceof \Contact)) {
			header("HTTP/1.0 404 Not Found");
			die();
		} // if

		if(!$user->isValidToken(array_var($_GET, 'token'))) {
			header("HTTP/1.0 404 Not Found");
			die();
		} // if

		\CompanyWebsite::instance()->setLoggedUser($user, false, false, false);
		return $user;
	} // loginUserByToken


    //
}
