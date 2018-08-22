SELECT v.id as 'id', v.photo as 'photo', v.nameEN as 'carnameEN', v.nameAR as 'carnameAR', v.year as 'year', v.series as 'series', v.vin as 'vin',
		v.mileage as 'mileage', v.price as 'price', v.owner as 'owner', v.nokeys as 'nokeys', v.acdamage as 'accident_damage', v.status as 'status', 
		v.is_available as 'is_available',   

		gBody.titleEN as 'bodystyleEN',
		gBody.titleAR as 'bodystyleAR',

		gMaker.titleEN as 'makerEN',
		gMaker.titleAR as 'makerAR',

		gTrans.titleEN as 'transmissionEN',
		gTrans.titleAR as 'transmissionAR',


		gDtrain.titleEN as 'driveTrainEN',
		gDtrain.titleAR as 'driveTrainAR',

		gEngine.titleEN as 'engineEN',
		gEngine.titleAR as 'engineAR',

		gFuel.titleEN as 'fuelEN',
		gFuel.titleAR as 'fuelAR',

		brands.nameEN as 'modelEN',
		brands.nameAR as 'modelAR'


		FROM vehicles v 
		INNER JOIN gsection gBody on v.bodystyle = gBody.id 
		INNER JOIN gsection gMaker on v.maker = gMaker.id 
		INNER JOIN gsection gTrans on v.trans = gTrans.id 
		INNER JOIN gsection gDtrain on v.dtrain = gDtrain.id 
		INNER JOIN gsection gEngine on v.engine = gEngine.id 
		INNER JOIN gsection gFuel on v.fuel = gFuel.id 
		INNER JOIN brands on v.model_id = brands.id 
		INNER JOIN v_options vo on v.id = vo.vehicle_id 
		INNER JOIN gsection gOptions on vo.options_id = gOptions.id  WHERE ( gOptions.titleEN IN ("AUX Input", "Backup Camera") AND vo.vehicle_id = v.id )   
		AND  v.mileage >= 10 AND  v.price >= 10  