<?php

/**
 * Select post where only Trusted Accounts are allowed to comment
 */
function trusted_accounts_meta_boxes() {
  add_meta_box( 'ta-1', __( 'Trusted Accounts', 'ta' ), 'ta_display_callback', 'post' );
}
add_action( 'add_meta_boxes', 'trusted_accounts_meta_boxes' );

function ta_display_callback( $post ) {
  include plugin_dir_path( __FILE__ ) . './layout/ta_post.php';
}

function ta_save_meta_box( $post_id ) {
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
  if ( $parent_id = wp_is_post_revision( $post_id ) ) {
      $post_id = $parent_id;
  }
  $fields = [
      'only_ta_post'
  ];
  foreach ( $fields as $field ) {
      update_post_meta( $post_id, $field, sanitize_text_field( $_POST[$field] ?? null ) );
   }
}
add_action( 'save_post', 'ta_save_meta_box' );

?>