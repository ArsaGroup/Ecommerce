<?php


namespace App\Http\Interfaces;

use Illuminate\Http\Request;

interface AdminControllerInterface
{
    public function view_category();

    public function add_category(Request $request);

    public function delete_category($id);

    public function view_product();

    public function add_product(Request $request);

    public function show_product();

    public function delete_product($id);

    public function update_product($id);

    public function update_product_confirm(Request $request, $id);

    public function order();

    public function delivered($id);

    public function print_pdf($id);

    public function send_user_email(Request $request, $id);

    public function searchdata(Request $request);
}
