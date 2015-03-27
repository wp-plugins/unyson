<?php if (!defined('FW')) die('Forbidden');
/**
 * Display extension in list on the extensions page
 * @var string $name
 * @var string $title
 * @var string $description
 * @var string $link
 * @var array $lists
 * @var array $nonces
 * @var string $default_thumbnail
 * @var bool $can_install
 */

$is_active = (bool)fw()->extensions->get($name);

if (isset($lists['installed'][$name])) {
	$installed_data = &$lists['installed'][$name];
} else {
	$installed_data = false;
}

if (isset($lists['available'][$name])) {
	$available_data = &$lists['available'][$name];
} else {
	$available_data = false;
}

{
	$thumbnail = $default_thumbnail;

	if (isset($lists['available'][$name])) {
		$thumbnail = $lists['available'][$name]['thumbnail'];
	}

	if (isset($lists['installed'][$name])) {
		$thumbnail = fw_akg('thumbnail', $lists['installed'][$name]['manifest'], $thumbnail);
	}
}
?>
<div class="fw-col-xs-12 fw-col-lg-6 fw-extensions-list-item<?php if ($installed_data && !$is_active): ?> disabled<?php endif; ?>" id="fw-ext-<?php echo esc_attr($name) ?>">
	<div class="inner">
		<div class="fw-extension-list-item-table">
			<div class="fw-extension-list-item-table-row">
				<div class="fw-extension-list-item-table-cell cell-1">
					<img height="128" src="<?php echo esc_attr($thumbnail) ?>" class="fw-extensions-list-item-thumbnail" alt="Thumbnail"/>
				</div>
				<div class="fw-extension-list-item-table-cell cell-2">
					<h3 class="fw-extensions-list-item-title"><?php
						if ($is_active && ($extension_link = fw()->extensions->get($name)->_get_link())) {
							echo fw_html_tag('a', array('href' => $extension_link), $title);
						} else {
							echo $title;
						}
					?></h3>

					<?php if ($description): ?>
						<p class="fw-extensions-list-item-desc"><?php echo $description; ?></p>
					<?php endif; ?>

					<?php
					if ($installed_data) {
						$_links = array();

						if ( $is_active && fw()->extensions->get( $name )->get_settings_options() ) {
							$_links[] = '<a href="' . esc_attr( $link ) . '&sub-page=extension&extension=' . esc_attr( $name ) . '">' . __( 'Settings', 'fw' ) . '</a>';
						}

						if ( $is_active && file_exists( $installed_data['path'] . '/readme.md.php' ) ) {
							if ( isset($lists['supported'][$name]) ) {
								// no sense to teach how to install the extension if theme is already configured and the is extension marked as compatible
							} else {
								$_links[] = '<a href="' . esc_attr( $link ) . '&sub-page=extension&extension=' . esc_attr( $name ) . '&tab=docs">' . __( 'Install Instructions', 'fw' ) . '</a>';
							}
						}

						if ( ! empty( $_links ) ):
							?><p
							class="fw-extensions-list-item-links"><?php echo implode( ' <span class="fw-text-muted">|</span> ', $_links ); ?></p><?php
						endif;

						unset( $_links );
					}
					?>
					<?php
					if (
						isset($lists['supported'][$name]) // is listed in the supported extensions list in theme manifest
						||
						($installed_data && $installed_data['is']['theme']) // is located in the theme
					): ?>
						<p><em><strong><span class="dashicons dashicons-yes"></span> <?php _e('Compatible', 'fw') ?></strong> <?php _e('with your current theme', 'fw') ?></em></p>
					<?php endif; ?>
				</div>
				<div class="fw-extension-list-item-table-cell cell-3">
					<?php if ($is_active): ?>
						<form action="<?php echo esc_attr($link) ?>&sub-page=deactivate&extension=<?php echo esc_attr($name) ?>" method="post">
							<?php wp_nonce_field($nonces['deactivate']['action'], $nonces['deactivate']['name']); ?>
							<input class="button" type="submit" value="<?php esc_attr_e('Deactivate', 'fw'); ?>"/>
						</form>
					<?php elseif ($installed_data): ?>
						<div class="fw-text-center">
							<form action="<?php echo esc_attr($link) ?>&sub-page=activate&extension=<?php echo esc_attr($name) ?>" method="post">
								<?php wp_nonce_field($nonces['activate']['action'], $nonces['activate']['name']); ?>
								<input class="button" type="submit" value="<?php esc_attr_e('Activate', 'fw'); ?>"/>
							</form>
							<?php
							/**
							 * Do not show the "Delete extension" button if the extension is not in the available list.
							 * If you delete such extension you will not be able to install it back.
							 * Most often these will be extensions located in the theme.
							 */
							if ($can_install && $available_data):
							?>
							<form action="<?php echo esc_attr($link) ?>&sub-page=delete&extension=<?php echo esc_attr($name) ?>"
							      method="post"
							      class="fw-extension-ajax-form"
							      data-confirm-message="<?php esc_attr_e('Are you sure you want to remove this extension?', 'fw') ?>">
								<?php wp_nonce_field($nonces['delete']['action'], $nonces['delete']['name']); ?>
								<p>
									<a href="#" onclick="jQuery(this).closest('form').submit(); return false;" data-remove-extension="<?php echo esc_attr($name) ?>" ><?php _e('Remove', 'fw'); ?></a>
								</p>
							</form>
							<?php endif; ?>
						</div>
					<?php elseif ($can_install && $available_data): ?>
						<form action="<?php echo esc_attr($link) ?>&sub-page=install&extension=<?php echo esc_attr($name) ?>" method="post" class="fw-extension-ajax-form">
							<?php wp_nonce_field($nonces['install']['action'], $nonces['install']['name']); ?>
							<input type="submit" class="button" value="<?php esc_attr_e('Download', 'fw') ?>">
						</form>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php if ($installed_data): ?>
			<?php if (!$is_active): ?>
				<?php if (!fw()->extensions->_get_db_active_extensions($name)): ?>
					<span><!-- Is not set as active in db --></span>
				<?php elseif ($installed_data['parent'] && !fw()->extensions->get($installed_data['parent'])): ?>
					<?php
					$parent_extension_name  = $installed_data['parent'];
					$parent_extension_title = fw_id_to_title($parent_extension_name);

					if (isset($lists['installed'][$parent_extension_name])) {
						$parent_extension_title = fw_akg('name', $lists['installed'][$parent_extension_name]['manifest'], $parent_extension_title);
					} elseif (isset($lists['available'][$parent_extension_name])) {
						$parent_extension_title = $lists['available'][$parent_extension_name]['name'];
					}
					?>
					<p class="fw-text-muted"><?php echo sprintf(__('Parent extension "%s" is disabled', 'fw'), $parent_extension_title); ?></p>
				<?php else: ?>
				<div class="fw-extension-disabled fw-border-box-sizing">
					<div class="fw-extension-disabled-panel fw-border-box-sizing">
						<div class="fw-row">
							<div class="fw-col-xs-12 fw-col-sm-3">
								<span class="fw-text-danger">!&nbsp;<?php _e('Disabled', 'fw'); ?></span>
							</div>
							<div class="fw-col-xs-12 fw-col-sm-9 fw-text-right">
							<?php
							$requirements = array();

							foreach (fw_akg('requirements', $installed_data['manifest'], array()) as $req_name => $req_data) {
								switch ($req_name) {
									case 'wordpress':
										if (empty($req_data['min_version']) && empty($req_data['max_version'])) {
											break;
										}

										global $wp_version;

										if ( ! empty( $req_data['min_version'] ) ) {
											if (!version_compare($req_data['min_version'], $wp_version, '<=')) {
												if ($can_install) {
													$requirements[] = sprintf(
														__( 'You need to update WordPress to %s: %s', 'fw' ),
														$req_data['min_version'],
														fw_html_tag( 'a', array( 'href' => self_admin_url( 'update-core.php' ) ), __( 'Update WordPress', 'fw' ) )
													);
												} else {
													$requirements[] = sprintf(
														__( 'WordPress needs to be updated to %s', 'fw' ),
														$req_data['min_version']
													);
												}
											}
										}

										if ( ! empty( $req_data['max_version'] ) ) {
											if (!version_compare($req_data['max_version'], $wp_version, '>=')) {
												$requirements[] = sprintf(
													__('Maximum supported WordPress version is %s', 'fw'),
													$req_data['max_version']
												);
											}
										}
										break;
									case 'framework':
										if (empty($req_data['min_version']) && empty($req_data['max_version'])) {
											break;
										}

										if ( ! empty( $req_data['min_version'] ) ) {
											if (!version_compare($req_data['min_version'], fw()->manifest->get_version(), '<=')) {
												if ($can_install) {
													$requirements[] = sprintf(
														__( 'You need to update %s to %s: %s', 'fw' ),
														fw()->manifest->get_name(),
														$req_data['min_version'],
														fw_html_tag( 'a', array( 'href' => self_admin_url( 'update-core.php' ) ),
															sprintf( __( 'Update %s', 'fw' ), fw()->manifest->get_name() )
														)
													);
												} else {
													$requirements[] = sprintf(
														__( '%s needs to be updated to %s', 'fw' ),
														fw()->manifest->get_name(),
														$req_data['min_version']
													);
												}
											}
										}

										if ( ! empty( $req_data['max_version'] ) ) {
											if (!version_compare($req_data['max_version'], fw()->manifest->get_version(), '>=')) {
												$requirements[] = sprintf(
													__( 'Maximum supported %s version is %s', 'fw' ),
													fw()->manifest->get_name(),
													$req_data['max_version']
												);
											}
										}
										break;
									case 'extensions':
										foreach ($req_data as $req_ext => $req_ext_data) {
											if ($ext = fw()->extensions->get($req_ext)) {
												if (empty($req_ext_data['min_version']) && empty($req_ext_data['max_version'])) {
													continue;
												}

												if ( ! empty( $req_ext_data['min_version'] ) ) {
													if (!version_compare($req_ext_data['min_version'], $ext->manifest->get_version(), '<=')) {
														if ($can_install) {
															$requirements[] = sprintf(
																__('You need to update the %s extension to %s: %s', 'fw'),
																$ext->manifest->get_name(),
																$req_ext_data['min_version'],
																fw_html_tag('a', array('href' => self_admin_url('update-core.php')),
																	sprintf(__('Update %s', 'fw'), $ext->manifest->get_name())
																)
															);
														} else {
															$requirements[] = sprintf(
																__('The %s extension needs to be updated to %s', 'fw'),
																$ext->manifest->get_name(),
																$req_ext_data['min_version']
															);
														}
													}
												}

												if ( ! empty( $req_ext_data['max_version'] ) ) {
													if (!version_compare($req_ext_data['max_version'], $ext->manifest->get_version(), '>=')) {
														$requirements[] = sprintf(
															__( 'Maximum supported %s extension version is %s', 'fw' ),
															$ext->manifest->get_name(),
															$req_ext_data['max_version']
														);
													}
												}
											} else {
												$ext_title = fw_id_to_title($req_ext);

												if (isset($lists['installed'][$req_ext])) {
													$ext_title = fw_akg('name', $lists['installed'][$req_ext]['manifest'], $ext_title);

													ob_start(); ?>
													<form action="<?php echo esc_attr($link) ?>&sub-page=activate&extension=<?php echo esc_attr($req_ext) ?>" method="post" style="display: inline;">
														<?php wp_nonce_field($nonces['activate']['action'], $nonces['activate']['name']); ?>
														<?php echo sprintf(__( 'The %s extension is disabled', 'fw' ), $ext_title); ?>:
														<a href="#" onclick="jQuery(this).closest('form').submit(); return false;"><?php echo sprintf(__('Activate %s', 'fw'), $ext_title); ?></a>
													</form>
													<?php
													$requirements[] = ob_get_clean();
												} else {
													if ($can_install && isset($lists['available'][$req_ext])) {
														$ext_title = $lists['available'][ $req_ext ]['name'];

														$requirements[] = sprintf(
															__( 'The %s extension is not installed: %s', 'fw' ),
															$ext_title,
															fw_html_tag( 'a', array( 'href' => $link . '&sub-page=install&extension=' . $req_ext ),
																sprintf( __( 'Install %s', 'fw' ), $ext_title )
															)
														);
													} else {
														$requirements[] = sprintf(
															__( 'The %s extension is not installed', 'fw' ),
															$ext_title
														);
													}
												}
											}
										}
										break;
									default:
										trigger_error('Invalid requirement: '. $req_name, E_USER_WARNING);
										continue;
								}
							}
							?>
							<a onclick="return false;" href="#" class="fw-extension-tip" title="<?php
								echo fw_htmlspecialchars(
									'<div class="fw-extension-tip-content">'.
									'<ul class="fw-extension-requirements"><li>- '. implode('</li><li>- ', $requirements) .'</li></ul>'.
									'</div>'
								);
								unset($requirements);
								?>"><?php _e('View Requirements', 'fw') ?></a>
								&nbsp; <p class="fw-visible-xs-block"></p><?php
									if ($can_install):
										?><a href="<?php echo esc_attr($link) ?>&sub-page=delete&extension=<?php echo esc_attr($name) ?>" class="button" ><?php _e('Remove', 'fw'); ?></a><?php
									endif;
								?>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php elseif ($available_data): ?>
			<!-- -->
		<?php else: ?>
			<!-- -->
		<?php endif; ?>
	</div>
</div>
