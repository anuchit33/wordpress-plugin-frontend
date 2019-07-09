<form method="POST">
  <?php wp_nonce_field( 'post-contact','csrf' ); ?>
  <div class="form-group">
    <label for="email">Email address</label>
    <input type="email" class="form-control" id="email" name ="email" aria-describedby="emailHelp" placeholder="Enter email">
  </div>

  <div class="form-group mt-3">
    <label for="message">Message</label>
    <textarea class="form-control" id="message"  name="message" rows="3" placeholder="Message..."></textarea>
  </div>
  <button type="submit" class="btn btn-primary btn-submit-contact" >Submit</button>
</form>