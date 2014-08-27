<?php
return array(
		'controllers' => array(
				'invokables' => array(
						"GarminVideos\Controller\VideoItem"
							=> "GarminVideos\Controller\VideoItemController",
						"GarminVideos\Controller\VideoItemRestful"
							=> "GarminVideos\Controller\VideoItemRestfulController",
						"GarminVideos\Controller\Index"
							=> "GarminVideos\Controller\IndexController"
				)
		),

		// The following section is new and should be added to your file
		'router' => array(
				'routes' => array(
						'garminvideos' => array(
								'type'    => 'segment',
								'options' => array(
										'route'    => '/garminvideos[/]',
										'defaults' => array(
											'controller' => 'GarminVideos\Controller\Index',
											'action'     => 'index',
										),
								),
								'may_terminate' => true,

								"child_routes" => array(
									"videolist" => array(
										"type" => "Segment",
										"options" => array(
											"route" => "video[/]",
											"defaults" => array(
												"controller" => "GarminVideos\Controller\VideoItem",
												"action" => "dispatch"
											)
										),
										"may_terminate" => true,
										"child_routes" => array(
											"videoactions" => array(
												"type" => "Segment",
												"options" => array(
													"route" => ":vid[/:_action][/]",
													"constraints" => array(
														"vid" => "([\w-]{11})|([\d]+)",
														"_action" => "[\w-]+"
													),
													"defaults" => array(
														"action" => "video"
													)
												),
												'may_terminate' => true
											),
											"videoactions1" => array(
													"type" => "Segment",
													"options" => array(
															"route" => ":_action/:vid[/]",
															"constraints" => array(
																	"vid" => "([\w-]{11})|([\d]+)",
																	"_action" => "[\w-]+"
															),
															"defaults" => array(
																	"action" => "video"
															)
													),
													'may_terminate' => true
											),
											"videoactions-add" => array(
													"type" => "Segment",
													"options" => array(
															"route" => ":_action[/]",
															"constraints" => array(
																	"_action" => "add"
															),
															"defaults" => array(
																	"action" => "video"
															)
													),
													'may_terminate' => true
											)
										)
									)	
 								)
						),
						
// 						"garminvideos-restful" => array(
// 							"type" => "method",
// 							"options" => array(
// 								"verb" => "get,post,delete",
// 								"route" => "/garminvideos/video/:id[/]",
// 								"constraints" => array(
// 									"id" => "[\w-]{11}"
// 								),
// 								"defaults" => array(
// 									"controller" => "GarminVideos\Controller\VideoItemRestful"
// 								)
// 							)
// 						)
				),
		),

		'view_manager' => array(
				'template_path_stack' => array(
						'garminvideos' => __DIR__ . '/../view',
				),
				'strategies' => array(
						'ViewJsonStrategy',
				),
				//'base_path' => '/garminvideos'
		),
);
?>