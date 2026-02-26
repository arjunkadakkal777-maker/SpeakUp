<!-- Logout Confirmation Modal -->
<div id="logoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="width: 60px; height: 60px; background: #ffe3e3; color: #e03131; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 20px;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
        </div>
        <h2 style="font-size: 20px; font-weight: 700; color: #1a1a1a; margin: 0 0 10px 0;">Sign Out?</h2>
        <p style="color: #666; font-size: 14px; margin: 0 0 25px 0;">Are you sure you want to end your session?</p>
        
        <div style="display: flex; gap: 10px;">
            <button type="button" onclick="closeLogoutModal()" style="flex: 1; padding: 12px; background: white; border: 1px solid #ddd; border-radius: 8px; font-weight: 600; cursor: pointer; color: #444;">Cancel</button>
            <a href="../logout.php" style="flex: 1; padding: 12px; background: #e03131; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; color: white; text-decoration: none; display: flex; align-items: center; justify-content: center;">Yes, Logout</a>
        </div>
    </div>
</div>

<script>
    function confirmLogout(event) {
        event.preventDefault();
        document.getElementById('logoutModal').style.display = 'flex';
    }
    
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Close on outside click
    window.onclick = function(event) {
        var modal = document.getElementById('logoutModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>
