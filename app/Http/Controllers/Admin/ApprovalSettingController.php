<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ApprovalSettingController extends Controller
{
    protected $link;

    function __construct() {}

    function index()
    {
        $parentLink = "Approval Setting";
        $link = $this->link;
        $active = "";

        return view(
            "pages.admin.approvalSetting",
            compact("link", "parentLink", "active"),
        );
    }
}
