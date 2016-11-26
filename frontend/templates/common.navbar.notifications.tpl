							<li id="notifications" class="dropdown">
								<a href="#" class="notification-button dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
									<span class="notification-icon glyphicon glyphicon-bell"></span>
									<span class="notification-counter label label-danger" data-bind="text: notifications().length, visible: notifications().length > 0, css: { 'label-danger': unread }"></span>
								</a>
								<ul class="dropdown-menu" data-bind="visible: notifications().length > 0">
									<li>
										<ul class="notification-drawer">
											<!-- ko foreach: notifications -->
											<li>
												<button type="button" class="close" aria-label="Close" data-bind="click: onCloseClicked, clickBubble: false"><span aria-hidden="true">×</span></button>
												<a data-bind="attr: { href: anchor }">
													<span data-bind="text: problem"></span> &mdash; <span data-bind="text: author"></span>
													<pre data-bind="text: message"></pre>
													<hr data-bind="visible: answer" />
													<pre data-bind="text: answer, visible: answer"></pre>
												</a>
											</li>
											<!-- /ko -->
										</ul>
									</li>
									<li role="separator" class="divider" data-bind="visible: notifications().length > 1"></li>
									<li data-bind="visible: notifications().length > 1">
										<a href="#" class="notification-clear" data-bind="click: onMarkAllAsRead">
											<span class="glyphicon glyphicon-align-right"></span> {#notificationsMarkAllAsRead#}
										</a>
									</li>
								</ul>
							</li>
