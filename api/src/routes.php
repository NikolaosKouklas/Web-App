<?php

$app->group('/api', function () use ($app) {
    
	$app->group('/v1', function () use ($app) {
        
		$app->get('/students', \Student::class . ':getAll');
        
        $app->get('/students/{id: [0-9]+}', \Student::class . ':getOne');
		
		$app->post('/students', \Student::class . ':create');
        
        $app->put('/students/{id: [0-9]+}', \Student::class . ':update');
	
		$app->delete('/students/{id: [0-9]+}', \Student::class . ':delete');
        
        /** FilterBy **/
        $app->post('/students/filterby/{fieldname}', \Student::class . ':filterby');
        /** OrderBy **/
        $app->post('/students/orderby/{fieldname}', \Student::class . ':orderby');
        /** Show students above a certain grade **/
        $app->post('/students/abovegrade', \Student::class . ':abovegrade');
        
        /** Search **/
        $app->get('/students/search/{keyword}', \Student::class . ':search');
        /** Paging **/
        $app->get('/students/paging/{page: [0-9]+}', \Student::class . ':getPaging');
	});
});
