<?php
return array(
		'controllers' => array(
				'invokables' => array(
						'Weixin\Controller\Weixin' => 'Weixin\Controller\WeixinCardController',
						"Weixin\Controller\Category" => "Weixin\Controller\CategoryController"
				),
		),

		// The following section is new and should be added to your file
		'router' => array(
				'routes' => array(
						'weixin' => array(
								'type'    => 'segment',
								'options' => array(
										'route'    => '/weixin[/][:action][/:id]',
										'constraints' => array(
												'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
												'id'     => '[0-9]+',
										),
										'defaults' => array(
												'controller' => 'Weixin\Controller\Weixin',
												'action'     => 'index',
										),
								),
						),
						"category" => array(
							"type" => "segment",
							"options" => array(
								"route" => "/wikipedia/category[/:id]",
								"constraints" => array(
									"id" => "[0-9]+"
								),
								"defaults" => array(
									"controller" => "Weixin\Controller\Category"
								)								
							),
						)
				),
		),

		'view_manager' => array(
				'template_path_stack' => array(
						'weixin' => __DIR__ . '/../view',
						'category' => __DIR__ . '/../view',
				),
				'strategies' => array(
						'ViewJsonStrategy',
				)
		),
);
?>