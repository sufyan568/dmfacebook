 <style type="text/css">.no_hover:hover{text-decoration: none;}</style>
 <section class="section">
  <div class="section-header">
    <h1><i class="fas fa-robot"></i> <?php echo $page_title; ?></h1>
    <div class="section-header-breadcrumb">
      <div class="breadcrumb-item"><?php echo $page_title; ?></div>
    </div>
  </div>

  <div class="section-body">
    <div class="row">
     
      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-cogs"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Bot Settings"); ?></h4>
            <p><?php echo $this->lang->line("Bot reply, persistent menu, sequence message etc"); ?></p>
            <a href="<?php echo base_url("messenger_bot/bot_list"); ?>" class="card-cta"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-th-large"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Post-back Manager"); ?></h4>
            <p><?php echo $this->lang->line("Postback ID & postback data management"); ?></p>
            <a href="<?php echo base_url("messenger_bot/template_manager"); ?>" class="card-cta"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-check-circle"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Whitelisted Domains"); ?></h4>
            <p><?php echo $this->lang->line("Whitelist domain for web url and other purposes"); ?></p>
            <a href="<?php echo base_url("messenger_bot/domain_whitelist"); ?>" class="card-cta"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
      </div>

      <?php if($this->is_engagement_exist) : ?>
      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-ring"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Messenger Engagement"); ?></h4>
            <p><?php echo $this->lang->line("Checkbox, send to messenger, customer chat, m.me"); ?></p>
            
            <div class="dropdown">
              <a href="#" data-toggle="dropdown" class="no_hover" style="font-weight: 500;"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
              <div class="dropdown-menu">
                <div class="dropdown-title"><?php echo $this->lang->line("Tools"); ?></div>                        
                <?php if($this->session->userdata('user_type') == 'Admin' || in_array(213,$this->module_access)) : ?><a class="dropdown-item has-icon" href="<?php echo base_url('messenger_bot_enhancers/checkbox_plugin_list'); ?>"><i class="fas fa-check-square"></i> <?php echo $this->lang->line("Checkbox Plugin"); ?></a><?php endif; ?>
                <?php if($this->session->userdata('user_type') == 'Admin' || in_array(214,$this->module_access)) : ?><a class="dropdown-item has-icon" href="<?php echo base_url('messenger_bot_enhancers/send_to_messenger_list'); ?>"><i class="fas fa-paper-plane"></i> <?php echo $this->lang->line("Send to Messenger"); ?></a><?php endif; ?>
                <?php if($this->session->userdata('user_type') == 'Admin' || in_array(215,$this->module_access)) : ?><a class="dropdown-item has-icon" href="<?php echo base_url('messenger_bot_enhancers/mme_link_list'); ?>"><i class="fas fa-link"></i> <?php echo $this->lang->line("m.me Link"); ?></a><?php endif; ?>
                <?php if($this->session->userdata('user_type') == 'Admin' || in_array(217,$this->module_access)) : ?><a class="dropdown-item has-icon" href="<?php echo base_url('messenger_bot_enhancers/customer_chat_plugin_list'); ?>"><i class="fas fa-comments"></i> <?php echo $this->lang->line("Customer Chat Plugin"); ?></a><?php endif; ?>
              </div>
            </div>

          </div>
        </div>
      </div>
      <?php endif; ?>


      <?php 
      if($this->session->userdata('user_type') == 'Admin' || in_array(257,$this->module_access)) : ?>
      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-save"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Saved Templates"); ?></h4>
            <p><?php echo $this->lang->line("Saved exported bot settings"); ?></p>
            <a href="<?php echo base_url("messenger_bot/saved_templates"); ?>" class="card-cta"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php 
      if($this->basic->is_exist("add_ons",array("project_id"=>31)))
      if($this->session->userdata('user_type') == 'Admin' || in_array(258,$this->module_access)) : ?>
      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-plug"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Json API Connector"); ?></h4>
            <p><?php echo $this->lang->line("Connect bot data with 3rd party apps"); ?></p>
            <a href="<?php echo base_url("messenger_bot_connectivity/json_api_connector"); ?>" class="card-cta"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
      </div>
      <?php endif; ?>


      <?php 
      if($this->basic->is_exist("add_ons",array("project_id"=>31)))
      if($this->session->userdata('user_type') == 'Admin' || in_array(261,$this->module_access)) : ?>
      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
           <i class="fab fa-wpforms"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Webform Builder"); ?></h4>
            <p><?php echo $this->lang->line("Custom data collection form for messenger bot"); ?></p>
            <a href="<?php echo base_url("messenger_bot_connectivity/webview_builder_manager"); ?>" class="card-cta"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
          </div>
        </div>
      </div>
      <?php endif; ?>    

      <?php if($this->session->userdata('user_type') == 'Admin' || in_array(265,$this->module_access)) : ?>
      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-paper-plane"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("Email Auto Responder"); ?></h4>
            <p><?php echo $this->lang->line("Add MailChimp API & Pull list"); ?></p>
            
            <div class="dropdown">
              <a href="#" data-toggle="dropdown" class="no_hover" style="font-weight: 500;"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>

              <div class="dropdown-menu">
                <div class="dropdown-title"><?php echo $this->lang->line("Tools"); ?></div>                        
                <a class="dropdown-item has-icon" href="<?php echo base_url('email_auto_responder_integration/mailchimp_list'); ?>"><i class="fas fa-check-square"></i> <?php echo $this->lang->line("MailChimp Integration"); ?></a>
              </div>
            </div>

          </div>
        </div>
      </div>
      <?php endif; ?>

      <?php 
      if($this->basic->is_exist("modules",array("id"=>266))) :
      if($this->session->userdata('user_type') == 'Admin' || in_array(266,$this->module_access)) : ?>
      <div class="col-lg-6">
        <div class="card card-large-icons">
          <div class="card-icon text-primary">
            <i class="fas fa-store"></i>
          </div>
          <div class="card-body">
            <h4><?php echo $this->lang->line("WooCommerce Abandoned Cart"); ?></h4>
            <p><?php echo $this->lang->line("Track cart/checkout, recover abandoned cart..."); ?></p>
            
            <div class="dropdown">
              <a href="<?php echo base_url('woocommerce_abandoned_cart'); ?>"><?php echo $this->lang->line("Actions"); ?> <i class="fas fa-chevron-right"></i></a>
            </div>

          </div>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>

    </div>
  </div>
</section>

