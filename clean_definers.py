import sys
import re

def clean_file(file_path):
    print(f"Cleaning DEFINERs from {file_path}...")
    with open(file_path, 'r', encoding='utf-8', errors='replace') as f:
        content = f.read()

    # 1. Replace /*!50013 DEFINER=`...`@`...` SQL SECURITY (DEFINER|INVOKER) */
    content = re.sub(
        r'/\*!50013\s+DEFINER=`[^`]+`@`[^`]+`\s+SQL\s+SECURITY\s+(?:DEFINER|INVOKER)\s*\*/',
        '/*!50013 SQL SECURITY INVOKER */',
        content
    )

    # 2. Replace standalone DEFINER=`...`@`...`
    content = re.sub(r'\bDEFINER=`[^`]+`@`[^`]+`\s*', '', content)

    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"Done cleaning {file_path}")

if __name__ == '__main__':
    for path in sys.argv[1:]:
        clean_file(path)
