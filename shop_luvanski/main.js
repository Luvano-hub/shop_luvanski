// Wait for the DOM to fully load
document.addEventListener('DOMContentLoaded', () => {
  
  // Attach click event to all 'Add to Cart' buttons
  document.querySelectorAll('.add-to-cart').forEach(btn =>
    btn.addEventListener('click', () => {
      const id = btn.dataset.id; // Get item ID from data attribute

      // Send POST request to add item to cart
      fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'item_id=' + id
      })
      .then(res => res.json())       // Parse the JSON response
      .then(() => alert('Added to cart')); // Show success alert
    })
  );

});
