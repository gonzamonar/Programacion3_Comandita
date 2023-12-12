<?php
interface IABMwithImages
{
	public static function getImgRootFolder() : string;
	public static function getDeletedImgFolder() : ?string;
	public static function SaveUploadedImage($request, $rootdir, $filename, $extension, $uploadParam) : bool;
}
