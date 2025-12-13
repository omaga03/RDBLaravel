
import sys

def sort_tables(all_objects_file, table_deps_file, view_deps_file):
    try:
        with open(all_objects_file, 'r', encoding='utf-8-sig') as f:
            objects = [line.strip() for line in f if line.strip()]
    except FileNotFoundError:
        print(f"Error: {all_objects_file} not found")
        return

    deps = []
    
    # helper to load deps
    def load_deps(filepath):
        loaded = []
        try:
            with open(filepath, 'r', encoding='utf-8-sig') as f:
                for line in f:
                    parts = line.strip().split('\t')
                    if len(parts) >= 2:
                        child, parent = parts[0], parts[1]
                        if child != parent:
                            loaded.append((child, parent))
        except FileNotFoundError:
            print(f"Error: {filepath} not found")
        return loaded

    deps.extend(load_deps(table_deps_file))
    deps.extend(load_deps(view_deps_file))

    adj = {t: set() for t in objects}
    in_degree = {t: 0 for t in objects}

    for child, parent in deps:
        if child not in adj:
             if child in objects: pass 
        if parent not in adj:
             if parent in objects: pass
        
        if child in adj and parent in adj:
             if child not in adj[parent]:
                 adj[parent].add(child)
                 in_degree[child] += 1

    queue = [t for t in objects if in_degree[t] == 0]
    result = []
    queue.sort()

    while queue:
        u = queue.pop(0)
        result.append(u)

        for v in adj[u]:
            in_degree[v] -= 1
            if in_degree[v] == 0:
                queue.append(v)
        queue.sort()

    if len(result) != len(objects):
        sys.stderr.write("Cycle detected or missing objects (Views/Tables)!\n")
        missing = set(objects) - set(result)
        sys.stderr.write(f"Missing/Cycle: {missing}\n")
        result.extend(list(missing))

    for t in result:
        try:
            sys.stdout.buffer.write((t + '\n').encode('utf-8'))
        except:
            print(t)

if __name__ == "__main__":
    sort_tables("all_objects.txt", "table_deps.txt", "view_deps.txt")
