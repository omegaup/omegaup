#include <cstdio>
#include <cstring>
#include <algorithm>
#include <sstream>
#include <fstream>
#include <unistd.h>

using namespace std;

void usage(int argc, char* argv[]) {
	printf("%s filename\n", argv[0]);
	printf("\tNormalizes the whitespace of a file by converting Windows-type newlines\n");
	printf("\tinto Unix-type newlines, deleting trailing whitespace from each line,\n");
	printf("\tand ensuring the file has a newline at the end.\n");
	exit(1);
}

int main(int argc, char* argv[]) {
	if (argc < 2) {
		usage(argc, argv);
	}

	char tmpfilename[1024];
	strcpy(tmpfilename, "/tmp/normalizrXXXXXX");

	FILE* f = fopen(argv[1], "r");
	if (f == NULL) {
		perror("fopen");
		exit(2);
	}

	int fd = mkstemp(tmpfilename);
	if (fd == -1) {
		fclose(f);
		perror("mkstemp");
		exit(2);
	}
	close(fd);

	FILE* o = fopen(tmpfilename, "w");
	if (o == NULL) {
		fclose(f);
		perror("fopen");
		exit(2);
	}

	int ch;
	bool endswithnewline = false;
	ostringstream buffer;

	// Set up the state machine that will (selectively) copy the bytes from
	// the original file to the temporary.
	while ((ch = fgetc(f)) != EOF) {
		switch (ch) {
			case '\r': {
				// Ignore, they only appear in windows CRLF.
				break;
			}

			case ' ':
			case '\t': {
				// Accumulate all whitespace characters in a
				// buffer.
				endswithnewline = false;
				buffer << (char)ch;
				break;
			}

			case '\n': {
				// Discard any contents in the buffer,
				// effectively trimming trailing whitespace.
				endswithnewline = true;
				buffer.seekp(0);
				buffer.str("");
				buffer.clear();
				fputc(ch, o);
				break;
			}

			default: {
				// A printable character. Write the buffer,
				// reset it, and write the character.
				endswithnewline = false;
				if (buffer.str().size() > 0) {
					fputs(buffer.str().c_str(), o);
					buffer.seekp(0);
					buffer.str("");
					buffer.clear();
				}
				fputc(ch, o);
				break;
			}
		}
	}

	// Unix files always end with a newline character.
	if (!endswithnewline) {
		fputc('\n', o);
	}

	fclose(f);
	fclose(o);

	// Copy the temporary file to its original location.
	std::ifstream src(tmpfilename);
	std::ofstream dst(argv[1]);

	if (dst) {
		dst << src.rdbuf();

		src.close();
		dst.close();
	} else {
		fprintf(stderr, "Cannot update %s\n", argv[1]);
		exit(3);
	}

	// Finally delete the temporary file.
	unlink(tmpfilename);
}
