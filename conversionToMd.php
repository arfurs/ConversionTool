<?php

	include 'conversion.class.php';

	function generate($sqlData){

		foreach($sqlData as $k => $v ){


			$priField = null;
			$uniField = null;
			if(array_key_exists('_pk',$v['field'])) $priField = $v['field']['_pk'];
			if(array_key_exists('_unique',$v['field'])) $uniField = $v['field']['_unique'];

			echo '### '.$k; // 表名
			echo '<br />';
			echo 'Field | Type | Null | Key | Default | Extra <br />
				---|---|---|---|---|---|'.'<br>';

			foreach($v['field'] as $kk => $vv){

				if($kk == '_unique' || $kk == '_pk') continue;

				// 输出markdown格式表格
				echo $kk.'|'.($vv['type'].($vv['unsigned']?' unsigned':'')).'|'.($vv['NULL']?'NULL':'NOT NULL').'|'.  ($kk == $priField ? 'PRI' : '') . ($kk == $uniField ? 'UNI' : '') .'|'.$vv['Default'].'|'.($vv['AUTO_INCREMENT']?'AUTO_INCREMENT':'none');

				echo "<br />";

				// 输出表引擎以及表字符集
			}

			echo "存储引擎：".$v['tableInfo']['engine'].' &nbsp;字符集：'.$v['tableInfo']['charset'];
			echo "<hr /><br>";

		}

	}


	$cs = (new conversion('a.sql'))->handleStructure();

	generate($cs);


