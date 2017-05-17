<?php
		return array(
			'VIEW_PATH'       =>'./Template/mobile/', // 改变某个模块的模板文件目录
			// 'DEFAULT_THEME'    =>'new', // 模板名称
			'DEFAULT_THEME'    =>'yundi', // 模板名称
			'TMPL_PARSE_STRING'  =>array(
		//                '__PUBLIC__' => '/Common', // 更改默认的/Public 替换规则
					// '__STATIC__'     => '/Template/mobile/new/Static', // 增加新的image  css js  访问路径  后面给 php 改了
					'__STATIC__'     => '/Template/mobile/yundi/Static', // 增加新的image  css js  访问路径  后面给 php 改了

                    '__STYLE__'      => '/Merchants_tpl/pc/default2',//--zhoufei 商户前端样式位置

			   ),
			   //'DATA_CACHE_TIME'=>60, // 查询缓存时间
			);
		?>