<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IssueRequestController;
use App\Http\Controllers\HitohaReferenceController;
use App\Http\Controllers\ProductionProgressController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\PressAssist\PressAssistItemController;
use App\Http\Controllers\PressAssist\PressAssistPositionController;
use App\Http\Controllers\PressAssist\PressAssistProcedureController;
use App\Http\Controllers\PressAssist\PressAssistParticularController;

#工場依頼出庫
Route::get('/issuerequest/home', [IssueRequestController::class, 'home'])->name('issuerequest.home');
Route::get('/issuerequest/verification', [IssueRequestController::class, 'verification'])->name('issuerequest.verification');
Route::post('/issuerequest/verification/regist', [IssueRequestController::class, 'registToStaging'])->name('issuerequest.verification.regist');
Route::get('/issuerequest/request', [IssueRequestController::class, 'request'])->name('issuerequest.request');
Route::post('/issuerequest/request/regist', [IssueRequestController::class, 'registToRequest'])->name('issuerequest.request.regist');
Route::post('/issuerequest/request/delete', [IssueRequestController::class, 'delete'])->name('issuerequest.request.delete');
Route::post('/issuerequest/request/update', [IssueRequestController::class, 'update'])->name('issuerequest.request.update');
Route::get('/issuerequest/history', [IssueRequestController::class, 'history'])->name('issuerequest.history');
Route::get('/issuerequest/getItem', [IssueRequestController::class, 'getItem'])->name('issuerequest.getItem');

#生産進捗表示
Route::get('/progressdisplay', [ProductionProgressController::class, 'index'])->name('progressdisplay');

//↓ 今後/hitohaが増えてきたらホームボタンからdepartment_code,line_sign,process_nameをパラメータで送りつつ遷移を考える
//Route::get('/hitoha/home', [HitohaReferenceController::class, 'index'])->name('hitoha.home');
Route::get('/hitoha/itemreference', [HitohaReferenceController::class, 'showItemReference'])->name('hitoha.itemreference');
Route::get('/hitoha/verification', [HitohaReferenceController::class, 'showVerification'])->name('hitoha.verification');
Route::get('/hitoha/fetchItem', [HitohaReferenceController::class, 'fetchItem'])->name('hitoha.fetchItem');
Route::post('/hitoha/registItem', [HitohaReferenceController::class, 'registItem'])->name('hitoha.registItem');
Route::get('/util/fetchusers', [UtilityController::class, 'fetchUsers'])->name('util.fetchusers');

// PressAssist
Route::prefix('PressAssist')->name('pressassist.')->group(function () {
    Route::get('/master', [PressAssistItemController::class, 'index'])->name('mst');
    Route::get('/master/item', [PressAssistItemController::class, 'item'])->name('mst.item');
    Route::get('/master/item/fetch', [PressAssistItemController::class, 'fetchItem'])->name('mst.item.fetch');
    Route::post('/master/item/regist', [PressAssistItemController::class, 'registItem'])->name('mst.item.regist');
    Route::post('/master/item/delete', [PressAssistItemController::class, 'deleteItem'])->name('mst.item.delete');
    Route::post('/master/item/export', [PressAssistItemController::class, 'exportItem'])->name('mst.item.export');
    Route::post('/master/item/import', [PressAssistItemController::class, 'importItem'])->name('mst.item.import');
    Route::get('/master/position', [PressAssistPositionController::class, 'position'])->name('mst.position');
    Route::get('/master/position/fetch', [PressAssistPositionController::class, 'fetchPosition'])->name('mst.position.fetch');
    Route::post('/master/position/regist', [PressAssistPositionController::class, 'registPosition'])->name('mst.position.regist');
    Route::post('/master/position/delete', [PressAssistPositionController::class, 'deletePosition'])->name('mst.position.delete');
    Route::post('/master/position/export', [PressAssistPositionController::class, 'exportPosition'])->name('mst.position.export');
    Route::post('/master/position/import', [PressAssistPositionController::class, 'importPosition'])->name('mst.position.import');
    Route::get('/master/procedure', [PressAssistProcedureController::class, 'procedure'])->name('mst.procedure');
    Route::get('/master/procedure/fetch', [PressAssistProcedureController::class, 'fetchProcedures'])->name('mst.procedure.fetch');
    Route::post('/master/procedure/regist', [PressAssistProcedureController::class, 'registProcedures'])->name('mst.procedure.regist');
    Route::post('/master/procedure/delete', [PressAssistProcedureController::class, 'deleteProcedures'])->name('mst.procedure.delete');
    Route::get('/master/procedure/preview', [PressAssistProcedureController::class, 'previewProcedures'])->name('mst.procedure.preview');
    Route::get('/master/procedure/particular/fetch', [PressAssistProcedureController::class, 'fetchParticularInstructions'])->name('mst.procedure.particular.fetch');
    Route::post('/master/procedure/particular/regist', [PressAssistProcedureController::class, 'registParticularInstruction'])->name('mst.procedure.particular.regist');
    Route::post('/master/procedure/particular/delete', [PressAssistProcedureController::class, 'deleteParticularInstruction'])->name('mst.procedure.particular.delete');
    Route::get('/master/procedure/particular/options', [PressAssistProcedureController::class, 'fetchParticularOptions'])->name('mst.procedure.particular.options');
    Route::get('/master/particular', [PressAssistParticularController::class, 'particular'])->name('mst.particular');
    Route::get('/master/particular/fetch', [PressAssistParticularController::class, 'fetchParticular'])->name('mst.particular.fetch');
    Route::post('/master/particular/regist', [PressAssistParticularController::class, 'registParticular'])->name('mst.particular.regist');
    Route::post('/master/particular/delete', [PressAssistParticularController::class, 'deleteParticular'])->name('mst.particular.delete');
    Route::post('/master/particular/export', [PressAssistParticularController::class, 'exportParticular'])->name('mst.particular.export');
    Route::post('/master/particular/import', [PressAssistParticularController::class, 'importParticular'])->name('mst.particular.import');
});
