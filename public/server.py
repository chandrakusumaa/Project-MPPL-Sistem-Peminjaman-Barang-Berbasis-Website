import http.server
import socketserver
import os

PORT = 8000
DIRECTORY = os.path.dirname(os.path.abspath(__file__))

class CleanURLHandler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=DIRECTORY, **kwargs)

    def end_headers(self):
        self.send_header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        self.send_header('Pragma', 'no-cache')
        self.send_header('Expires', '0')
        super().end_headers()

    def do_GET(self):
        # If path doesn't end with slash and doesn't have an extension, try appending .html
        path_without_query = self.path.split('?')[0]
        query = ('?' + self.path.split('?')[1]) if '?' in self.path else ''
        if not path_without_query.endswith('/') and '.' not in os.path.basename(path_without_query):
            potential_file = os.path.join(DIRECTORY, path_without_query.lstrip('/') + '.html')
            if os.path.isfile(potential_file):
                self.path = path_without_query + '.html' + query
        return super().do_GET()

    def do_HEAD(self):
        path_without_query = self.path.split('?')[0]
        query = ('?' + self.path.split('?')[1]) if '?' in self.path else ''
        if not path_without_query.endswith('/') and '.' not in os.path.basename(path_without_query):
            potential_file = os.path.join(DIRECTORY, path_without_query.lstrip('/') + '.html')
            if os.path.isfile(potential_file):
                self.path = path_without_query + '.html' + query
        return super().do_HEAD()

if __name__ == '__main__':
    socketserver.TCPServer.allow_reuse_address = True
    with socketserver.TCPServer(("", PORT), CleanURLHandler) as httpd:
        print(f"Serving at http://localhost:{PORT}")
        httpd.serve_forever()
