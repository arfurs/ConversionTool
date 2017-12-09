<?php

	class conversion{

		public $sqlFile;
		public $tableNameArr = array();

        /**
        * 传入要生成markdown表格形式的sql表结构文件路径
        * @param sql表结构文件的路径
        * @return void
        */
		public function __construct($path){

            if(!file_exists($path)){
                die('请指定sql文件路径');
            }else{
                $nameArr = explode('.',$path);
                $suffix = $nameArr[count($nameArr)-1];
                if($suffix != 'sql'){
                    die('该文件不是sql文件');
                }
            }

			$this->sqlFile = file_get_contents($path);
			$this->tableNameArr = $this->getTableName();
		}

        /**
         * 获取当前sql文件中的所有表名称
         * @access public
         * @return array 由表名组成的1维数组
         */
		public function getTableName(){
			$exp = "/TABLE\s`(?<tableName>.*?)`\s/";
			preg_match_all($exp,$this->sqlFile,$result);
			$nameArr = [];
			foreach($result['tableName'] as $v){
				$nameArr[] = $v;
			}
			return $nameArr;
		}

        /**
         * 获取sql文件中所有字符串形式表结构
         * @access public
         * @return array 由每张数据表字符串组成的一维数组
         */
		public function getStructureStr(){
			$exp = "/TABLE\s`.*`\s\((?<structure>[\w\W]*?)\)\sENGINE=(?<engine>.*?)\s.*?DEFAULT\sCHARSET=(?<charset>\w*)/";
			preg_match_all($exp,$this->sqlFile,$result);
			return $result;
		}

        /**
         * 将字符串形式的表结构处理数组形式
         */
		public function handleStructure(){
            $structureStr = $this->getStructureStr();
			$st_tmp_arr = $structureStr['structure'];
			$st_arr = array();
			foreach($st_tmp_arr as  $k => &$v){
			    $st_arr[$this->tableNameArr[$k]]['field'] = $this->handleTableField($v);
			    $st_arr[$this->tableNameArr[$k]]['tableInfo']['engine'] = $structureStr['engine'][$k];
			    $st_arr[$this->tableNameArr[$k]]['tableInfo']['charset'] = $structureStr['charset'][$k];
			}
			return $st_arr;
		}

        /**
         * 处理成markdown形式的
         * @param $structure 字符串形式的表结构
         * @return array 由字段组成的数组
         */
		public function handleTableField($structure){
		    $everyField = explode(PHP_EOL,$structure);
		    $fieldArr = array();
            foreach($everyField as $key => $field){
                if(!$field) continue;
                $field = rtrim($field,',');
                preg_match('/`(.*?)`/',$field,$res);
                if(preg_match('/PRIMARY KEY/',$field)){
                    $fieldArr['_pk'] = $res[1];
                }else if(preg_match('/UNIQUE KEY/',$field)){
                    $fieldArr['_unique'] = $res[1];
                }else{
                    $fieldArr[@$res[1]] = $this->checkFiledType($field);
                }
            }
            return $fieldArr;
		}

        /**
         * 将一整行的字段信息(string) 处理成数组形式 对应的键名显示出该字段的类型 约束条件 是否为空等
         * @param $filedStr 字段的一整行字符串 包括字段类型 约束条件 是否为空等条件在内的一个字符串
         * @reutrn array 处理完后的数据
         */
		public function checkFiledType($fieldStr){

            $field_arr = array();

            // 查找该字段的字段类型正则
            $f_typeExp = '/`.*`\s([\w\W]*?)(?:\s|$)/';
            preg_match($f_typeExp,$fieldStr,$result);
            $field_arr['type'] = $result[1];

            // 查找默认值
            $f_defaultExp = '/DEFAULT\s(.*?)$/';
            if(preg_match($f_defaultExp,$fieldStr,$result)){
                $field_arr['Default'] = $result[1];
            }else{
                $field_arr['Default'] = 'NULL';
            }

            // unsigned
            $field_arr['unsigned'] = (bool)preg_match('/unsigned/',$fieldStr);

            // NULL
            $field_arr['NULL'] = (bool)!preg_match('/NOT NULL/',$fieldStr);

            // AUTO_INCREMENT
            $field_arr['AUTO_INCREMENT'] = (bool)preg_match('/AUTO_INCREMENT/',$fieldStr);

            return $field_arr;

        }

	}

	
	

