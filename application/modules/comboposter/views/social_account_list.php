<style>

	.makeScroll {height: 500px;overflow: hidden;}
	@media (max-width: 575.98px) {
		.avatar-item .avatar-badge {
		    position: absolute;
		    bottom: 5px;
		    right: 95px;
		    margin-right: 0;
		}
	}
</style>

<section class="section">
	<div class="section-header">
		<h1><i class="far fa-list-alt"></i> <?php echo $this->lang->line('Social Accounts'); ?></h1>
		<div class="section-header-breadcrumb">
		  <div class="breadcrumb-item"><a href="<?php echo base_url('ultrapost'); ?>"><?php echo $this->lang->line('Comboposter'); ?></a></div>
		  <div class="breadcrumb-item"><?php echo $this->lang->line('Social Accounts'); ?></div>
		</div>
	</div>

	<?php 

		if($this->session->userdata('account_import_error') != '') {

			echo "<div class='alert alert-danger text-center'><i class='fas fa-check-circle'></i> ".$this->session->userdata('account_import_error')."</div>";
			$this->session->unset_userdata('account_import_error');
		}

		
		if($this->session->userdata('limit_cross') != '') {

			echo "<div class='alert alert-danger text-center'><i class='fas fa-check-circle'></i> ".$this->session->userdata('limit_cross')."</div>";
			$this->session->unset_userdata('limit_cross');
		}

	 ?>

	<div class="section-body">
		<div class="row">
			<?php if($this->session->userdata('user_type') == 'Admin' || in_array(102,$this->module_access)) : ?>
				<div class="col-12 col-sm-12 col-lg-6 ">
					<div class="card makeScroll">
					  <div class="card-header">
					    <h4><i class="fab fa-twitter"></i> <?php echo $this->lang->line('Twitter Accounts'); ?></h4>
					    <div class="card-header-action youtube_button">
					    	<?php echo $twitter_login_button; ?>
					    </div>
					  </div>
					  <div class="card-body">
					    
					        
					        <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
					        	<?php foreach ($twitter_account_list as $key => $single_account): ?>

					        		<li class="media">
					        		    <?php $img_src = $single_account['profile_image']; ?>
		        		    	    	<div class="avatar-item" style="margin-right:20px;">
		        		        	  		<img alt="image" width="50" src="<?php echo $img_src; ?>" class="img-fluid">
		        		        	  		<div class="avatar-badge delete_account" title="<?php echo $this->lang->line("Delete Account"); ?>" social_media="twitter" table_id="<?php echo $single_account['id']; ?>" data-toggle="tooltip">
		        		    	    	  		<a href="#"><i class="fas fa-trash-alt red" style="margin-left: 0;"></i></a>
		        		    	    	  	</div>
		        		    	    	</div>
					        			
					        		    <div class="media-body">
					        		      <div class="media-title"><?php echo $single_account['name']; ?></div>
					        		      <div class="text-job text-muted"><?php echo $single_account['screen_name'] ?></a></div>
					        		    </div>
					        		    <div class="media-items">
					        		      <div class="media-item">
					        		        <div class="media-value"><?php echo $single_account['followers']; ?></div>
					        		        <div class="media-label"><?php echo $this->lang->line('Followers'); ?></div>
					        		      </div>
					        		    </div>
					        		</li>
					        		

					        	<?php endforeach ?>
						        
					        </ul>
					      
					  </div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->session->userdata('user_type') == 'Admin' || in_array(33,$this->module_access)) : ?>			
				<div class="col-12 col-sm-12 col-lg-6">
					<div class="card makeScroll">
						<div class="card-header">
						    <h4><i class="fab fa-youtube"></i> <?php echo $this->lang->line('Youtube Channels') ?></h4>
						    <div class="card-header-action">
						    	<?php echo $youtube_login_button; ?>
						    </div>
						</div>
						<div class="card-body">
						    <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
						    	<?php foreach ($youtube_channel_list as $key => $single_channel): ?>
								    <li class="media">
								    	<div class="avatar-item" style="margin-right:20px;">
							    	  		<img alt="image" width="50" src="<?php echo $single_channel['profile_image']; ?>" class="img-fluid">
							    	  		<div class="avatar-badge delete_account" title="<?php echo $this->lang->line("Delete Account"); ?>" social_media="youtube" table_id="<?php echo $single_channel['id']; ?>" data-toggle="tooltip">
								    	  		<a href="#"><i class="fas fa-trash-alt red" style="margin-left: 0;"></i></a>
								    	  	</div>
								    	</div>
								        
								        <div class="media-body">
							          		<div class="media-title">
							          			<?php echo (strlen($single_channel['title']) < 15 ) ? $single_channel['title'] : substr($single_channel['title'], 0,12).'...'; ?>
						          			</div>
								          	<div class="text-job text-muted"><?php echo $single_channel['channel_id']; ?></div>
								        </div>
								        <div class="media-items">
						          			<!-- <div class="media-item">
								            	<div class="media-value"><?php echo $single_channel['view_count']; ?></div>
								            	<div class="media-label"><?php echo $this->lang->line('Views'); ?></div>
								          	</div> -->
								          	<div class="media-item">
								            	<div class="media-value"><?php echo $single_channel['video_count']; ?></div>
								            	<div class="media-label"><?php echo $this->lang->line('Videos') ?></div>
								          	</div>
								          	<div class="media-item">
								            	<div class="media-value"><?php echo $single_channel['subscriber_count']; ?></div>
								            	<div class="media-label"><?php echo $this->lang->line('Subscribers') ?></div>
								          	</div>
								        </div>
								    </li>
						    	<?php endforeach ?>
						    </ul>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->session->userdata('user_type') == 'Admin' || in_array(103,$this->module_access)) : ?>
				<div class="col-12 col-sm-12 col-lg-6 ">
					<div class="card makeScroll">
					  <div class="card-header">
					    <h4><i class="fab fa-linkedin"></i> <?php echo $this->lang->line('Linkedin Accounts'); ?></h4>
					    <div class="card-header-action youtube_button">
					    	<?php echo $linkedin_login_button; ?>
					    </div>
					  </div>
					  <div class="card-body">
					    
					        
					        <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
					        	<?php foreach ($linkedin_account_list as $key => $single_account): ?>

					        		<li class="media">
					        		    <?php $img_src = $single_account['profile_pic']; ?>
		        		    	    	<div class="avatar-item" style="margin-right:20px;">
		        		        	  		<img alt="image" width="50" src="<?php echo $img_src; ?>" class="img-fluid">
		        		        	  		<div class="avatar-badge delete_account" title="<?php echo $this->lang->line("Delete Account"); ?>" social_media="linkedin" table_id="<?php echo $single_account['id']; ?>" data-toggle="tooltip">
		        		    	    	  		<a href="#"><i class="fas fa-trash-alt red" style="margin-left: 0;"></i></a>
		        		    	    	  	</div>
		        		    	    	</div>
					        		    <div class="media-body">
					        		      <div class="media-title" style="padding-top: 12px;"><?php echo $single_account['name']; ?></div>
					        		      <div class="text-job text-muted"><?php echo $single_account['linkedin_id'] ?></a></div>
					        		    </div>
					        		    
					        		</li>
					        		

					        	<?php endforeach ?>
						        
					        </ul>
					      
					  </div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->session->userdata('user_type') == 'Admin' || in_array(105,$this->module_access)) : ?>
				<div class="col-12 col-sm-12 col-lg-6 ">
					<div class="card makeScroll">
					  <div class="card-header">
					    <h4><i class="fab fa-reddit-square"></i> <?php echo $this->lang->line('Reddit Accounts'); ?></h4>
					    <div class="card-header-action youtube_button">
					    	<?php echo $reddit_login_button; ?>
					    </div>
					  </div>
					  <div class="card-body">
					    
					        
					        <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
					        	<?php foreach ($reddit_account_list as $key => $single_account): ?>

					        		<li class="media">
					        		    <?php $img_src = $single_account['profile_pic']; ?>
		        		    	    	<div class="avatar-item" style="margin-right:20px;">
		        		        	  		<img alt="image" width="50" src="<?php echo $img_src; ?>" class="img-fluid">
		        		        	  		<div class="avatar-badge delete_account" title="<?php echo $this->lang->line("Delete Account"); ?>" social_media="reddit" table_id="<?php echo $single_account['id']; ?>" data-toggle="tooltip">
		        		    	    	  		<a href="#"><i class="fas fa-trash-alt red" style="margin-left: 0;"></i></a>
		        		    	    	  	</div>
		        		    	    	</div>
					        		    <div class="media-body">
					        		      <div class="media-title"><?php echo $single_account['username']; ?></div>
					        		      <div class="text-job text-muted"><a href="<?php echo 'https://www.reddit.com'.$single_account['url'] ?>" target="_BLANK"><?php echo $this->lang->line("Visit Reddit"); ?></a></div>
					        		    </div>
					        		    
					        		</li>
					        		

					        	<?php endforeach ?>
						        
					        </ul>
					      
					  </div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->session->userdata('user_type') == 'Admin' || in_array(101,$this->module_access)) : ?>	
				<div class="col-12 col-sm-12 col-lg-6 ">
					<div class="card makeScroll">
					  <div class="card-header">
					    <h4><i class="fab fa-pinterest-square"></i> <?php echo $this->lang->line('Pinterest Accounts'); ?></h4>
					    <div class="card-header-action youtube_button">
					    	<?php echo $pinterest_login_button; ?>
					    </div>
					  </div>
					  <div class="card-body">
					    
					        
					        <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
					        	<?php foreach ($pinterest_account_list as $key => $single_account): ?>

					        		<li class="media">
					        		    <?php $img_src = $single_account['image']; ?>
		        		    	    	<div class="avatar-item" style="margin-right:20px;">
		        		        	  		<img alt="image" width="50" src="<?php echo $img_src; ?>" class="img-fluid">
		        		        	  		<div class="avatar-badge delete_account" title="<?php echo $this->lang->line("Delete Account"); ?>" social_media="pinterest" table_id="<?php echo $single_account['id']; ?>" data-toggle="tooltip">
		        		    	    	  		<a href="#"><i class="fas fa-trash-alt red" style="margin-left: 0;"></i></a>
		        		    	    	  	</div>
		        		    	    	</div>
					        		    <div class="media-body">
					        		      <div class="media-title" style="padding-top: 12px;"><?php echo $single_account['name']; ?></div>
					        		      <div class="text-job text-muted"><?php echo $single_account['user_name'] ?></a></div>
					        		    </div>
					        		    <div class="media-items">
					        		      <div class="media-item">
					        		        <div class="media-value"><?php echo $single_account['boards']; ?></div>
					        		        <div class="media-label"><?php echo $this->lang->line('Boards'); ?></div>
					        		      </div>
					        		      <div class="media-item">
					        		        <div class="media-value"><?php echo $single_account['pins']; ?></div>
					        		        <div class="media-label"><?php echo $this->lang->line('Pins'); ?></div>
					        		      </div>
					        		    </div>
					        		    
					        		</li>
					        		

					        	<?php endforeach ?>
						        
					        </ul>
					      
					  </div>
					</div>
				</div>
			<?php endif; ?>	

			<?php if($this->session->userdata('user_type') == 'Admin' || in_array(107,$this->module_access)) : ?>
				<div class="col-12 col-sm-12 col-lg-6 ">
					<div class="card makeScroll">
					  <div class="card-header">
					    <h4><i class="fab fa-blogger"></i> <?php echo $this->lang->line('Blogger Accounts'); ?></h4>
					    <div class="card-header-action youtube_button">
					    	<?php echo $blogger_login_button; ?>
					    </div>
					  </div>
					  <div class="card-body">
					    
					        
					        <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
					        	<?php foreach ($blogger_account_list as $key => $single_account): ?>

					        		<li class="media">
					        		    <?php $img_src = $single_account['picture']; ?>
		        		    	    	<div class="avatar-item" style="margin-right:20px;">
		        		        	  		<img alt="image" width="50" src="<?php echo $img_src; ?>" class="img-fluid">
		        		        	  		<div class="avatar-badge delete_account" title="<?php echo $this->lang->line("Delete Account"); ?>" social_media="blogger" table_id="<?php echo $single_account['id']; ?>" data-toggle="tooltip">
		        		    	    	  		<a href="#"><i class="fas fa-trash-alt red" style="margin-left: 0;"></i></a>
		        		    	    	  	</div>
		        		    	    	</div>
					        		    <div class="media-body">
					        		      <div class="media-title" style="padding-top: 12px;"><?php echo $single_account['name']; ?></div>
					        		      <div class="text-job text-muted"><?php echo $single_account['blogger_id'] ?></a></div>
					        		    </div>
					        		    <div class="media-items">
					        		      <div class="media-item">
					        		        <div class="media-value"><?php echo $single_account['blog_count']; ?></div>
					        		        <div class="media-label"><?php echo $this->lang->line('Blogs'); ?></div>
					        		      </div>
					        		    </div>
					        		    
					        		</li>
					        		

					        	<?php endforeach ?>
						        
					        </ul>
					      
					  </div>
					</div>
				</div>
			<?php endif; ?>

			<?php if($this->session->userdata('user_type') == 'Admin' || in_array(108,$this->module_access)) : ?>
				<div class="col-12 col-sm-12 col-lg-6 ">
					<div class="card makeScroll">
					  <div class="card-header">
					    <h4><i class="fab fa-wordpress"></i> <?php echo $this->lang->line('Wordpress Accounts'); ?></h4>
					    <div class="card-header-action youtube_button">
					    	<?php echo $wordpress_login_button; ?>
					    </div>
					  </div>
					  <div class="card-body">
					    
					        
					        <ul class="list-unstyled user-details list-unstyled-border list-unstyled-noborder">
					        	<?php foreach ($wordpress_account_list as $key => $single_account): ?>

					        		<li class="media">
					        		    <?php $img_src = $single_account['icon']; ?>
		        		    	    	<div class="avatar-item" style="margin-right:20px;">
		        		        	  		<img alt="image" width="50" src="<?php echo $img_src; ?>" class="img-fluid">
		        		        	  		<div class="avatar-badge delete_account" title="<?php echo $this->lang->line("Delete Account"); ?>" social_media="wordpress" table_id="<?php echo $single_account['id']; ?>" data-toggle="tooltip">
		        		    	    	  		<a href="#"><i class="fas fa-trash-alt red" style="margin-left: 0;"></i></a>
		        		    	    	  	</div>
		        		    	    	</div>
					        		    <div class="media-body">
					        		      <div class="media-title" style="padding-top: 12px;"><?php echo $single_account['name']; ?></div>
					        		      <div class="text-job text-muted"><?php echo $single_account['blog_id'] ?></a></div>
					        		    </div>
					        		    <div class="media-items">
					        		      <div class="media-item">
					        		        <div class="media-value"><?php echo $single_account['posts']; ?></div>
					        		        <div class="media-label"><?php echo $this->lang->line('Posts'); ?></div>
					        		      </div>
					        		    </div>
					        		    
					        		</li>
					        		

					        	<?php endforeach ?>
						        
					        </ul>
					      
					  </div>
					</div>
				</div>
			<?php endif; ?>


		</div>
	</div>


<script>
	$(document).ready(function() {
		$(document).on('click', '.delete_account', function(event) {
			event.preventDefault();

			swal({
			  title: 'Are you sure?',
			  text: 'Do you really want to delete this account? If you delete this account it will be deleted from your database.',
			  icon: 'warning',
			  buttons: true,
			  dangerMode: true,
			})
			.then((willDelete) => {
			  if (willDelete) 
			  {
			    let social_media = $(this).attr('social_media');
			    let table_id = $(this).attr('table_id');

			    $.ajax({
			      context: this,
			      type:'POST',
			      dataType: 'json',
			      url:"<?php echo base_url('comboposter/delete_social_account'); ?>",
			      data:{social_media: social_media, table_id: table_id},
			      success:function(response){ 

			        if (response.status == 'success') {
			          iziToast.success({title: '', message: response.message, position: 'bottomRight'});
			        } else if (response.status == 'error') {
			          iziToast.error({title: '',message: response.message, position: 'bottomRight'});
			        }

			        window.location.href = "<?php echo base_url('comboposter/social_accounts'); ?>";
			      }
			    });
			  } 
			});
		});
	});
</script>