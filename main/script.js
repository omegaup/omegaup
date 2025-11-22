const editor = document.getElementById('editor');
const preview = document.getElementById('preview');
let saveTimeout;

function saveContent() {
  const content = editor.value;
  preview.textContent = content;
  console.log("Auto-saved:", content);
}

// Auto-save after 1 second of inactivity
editor.addEventListener('input', () => {
  clearTimeout(saveTimeout);
  saveTimeout = setTimeout(saveContent, 1000);
});