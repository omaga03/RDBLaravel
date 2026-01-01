
import re

with open('resources/views/frontend/rdbproject/index.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

open_divs = len(re.findall(r'<div', content, re.IGNORECASE))
close_divs = len(re.findall(r'</div>', content, re.IGNORECASE))

print(f"Open: {open_divs}")
print(f"Close: {close_divs}")
