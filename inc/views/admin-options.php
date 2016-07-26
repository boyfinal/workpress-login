<div class="wrap">
	<h2> Theme Option Panel</h2>
	<form action="options.php" method="post">
		<?php settings_fields('farost_login_options'); ?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><?php _e("Display Social Login", 'farost_login'); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php _e("Turn on Social Login", 'farost_login'); ?></span>
							</legend>
							<label for="farost_login_on">
								<input id="farost_login_on" name="farost_login[display]" onclick="" type="checkbox" <?php echo farost_login_option('display') ? 'checked = "checked"' : '';?> value="1" />Turn On Social Login
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><?php _e("Select providers", 'farost_login'); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php _e("Select providers", 'farost_login'); ?></span>
							</legend>
							<label for="farost_login_facebook">
								<input id="farost_login_facebook" name="farost_login[providers][]" type="checkbox" <?php echo (is_array(farost_login_option('providers')) && in_array('facebook', farost_login_option('providers'))) ? 'checked = "checked"' : '';?> value="facebook" /><?php _e("Facebook", 'farost_login'); ?>
							</label>
							<br>
							<label for="farost_login_twitter">
								<input id="farost_login_twitter" name="farost_login[providers][]" type="checkbox" <?php echo (is_array(farost_login_option('providers')) && in_array('twitter', farost_login_option('providers'))) ? 'checked = "checked"' : '';?> value="twitter" /><?php _e("Twitter", 'farost_login'); ?>
							</label>
							<br>
							<label for="farost_login_facebook">
								<input id="farost_login_google" name="farost_login[providers][]" type="checkbox" <?php echo (is_array(farost_login_option('providers')) && in_array('google', farost_login_option('providers'))) ? 'checked = "checked"' : '';?> value="google" /><?php _e("Google+", 'farost_login'); ?>
							</label>
							<br>
							<p class="description"><?php _e('Select Social ID provider to enable in Social Login', 'farost_login') ?></p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th>
						<label for="farost_login_fbkey"><?php _e("Facebook App ID", 'farost_login'); ?></label>
					</th>
					<td>
						<input id="farost_login_fbkey" name="farost_login[fb_key]" type="text" value="<?php echo farost_login_option('fb_key'); ?>" class="regular-text ltr" />
						<p class="description">
							<?php _e('Paste following url in <strong>Site URL</strong> settings', 'farost_login'); ?>: 
							<strong style="color: #14ACDF"><?php echo home_url(); ?></strong>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="farost_login_twitter_key"><?php _e("Twitter API Key", 'farost_login'); ?></label>
					</th>
					<td>
						<input id="farost_login_twitter_key" name="farost_login[twitter_key]" type="text" value="<?php echo farost_login_option('twitter_key'); ?>" class="regular-text ltr"/>
						<p class="description">
							<?php _e('Paste following url in <strong>Website</strong> and <strong>Callback URL</strong> settings', 'farost_login'); ?>: 
							<strong style="color: #14ACDF"><?php echo home_url(); ?></strong>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="farost_login_twitter_secret"><?php _e("Twitter API Secret", 'farost_login'); ?></label>
					</th>
					<td>
						<input id="farost_login_twitter_secret" name="farost_login[twitter_secret]" type="text" value="<?php echo farost_login_option('twitter_secret'); ?>" class="regular-text ltr"/>
						<p class="description">
							<?php _e('Paste following url in <strong>Website</strong> and <strong>Callback URL</strong> settings', 'farost_login'); ?>: 
							<strong style="color: #14ACDF"><?php echo home_url(); ?></strong>
						</p>
					</td>
				</tr>
				<tr>
					<th>
						<label for="the_champ_gplogin_key"><?php _e("Google+ Client ID", 'farost_login'); ?></label>
					</th>
					<td>
						<input id="the_champ_gplogin_key" name="farost_login[google_key]" type="text" value="<?php echo farost_login_option('google_key'); ?>" class="regular-text ltr"/>
						<p class="description">
							<?php _e('Paste following url in <strong>Authorized JavaScript origins</strong> and <strong>Authorized redirect URIs</strong> settings', 'farost_login'); ?>: 
							<strong style="color: #14ACDF"><?php echo home_url(); ?></strong>
						</p>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="save" class="button button-primary" value="<?php _e("Save Changes", 'farost_login'); ?>" />
		</p>
	</form>
</div>