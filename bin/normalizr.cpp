#include <cstdio>
#include <cstring>
#include <algorithm>
#include <sstream>
#include <fstream>

#define ERR(error_code, filename, description) \
	do { \
		fprintf(stderr, "%s: %s\n", filename, description); \
		return error_code; \
	} while (false)

using namespace std;

// Creates a temporary file automatically deletes it on object destruction.
class TmpFile {
public:
	TmpFile(const char* prefix) {
		snprintf(filename_, sizeof(filename_) - 10, "/tmp/%s", prefix);
		strcat(filename_, "XXXXXX");
		int fd = ::mkstemp(filename_);
		valid_ = (fd != -1);

		if (valid_) {
			::close(fd);
		}
	}

	~TmpFile() {
		if (valid_) {
			::unlink(filename_);
		}
	}

	bool operator!() const {
		return !valid_;
	}

	const char* str() const {
		return filename_;
	}

private:
	// The name of the temporary file.
	char filename_[256];

	// True if the temporary file was successfully created.
	bool valid_;
};

void usage(int argc, char* argv[]) {
	printf("%s filename\n", argv[0]);
	printf("\tNormalizes the whitespace of a file by converting Windows-type newlines\n");
	printf("\tinto Unix-type newlines, deleting trailing whitespace from each line,\n");
	printf("\tand ensuring the file has a newline at the end.\n");
	exit(1);
}

int normalize(const char* filename) {
	ifstream f(filename);
	if (!f) {
		ERR(2, filename, "Cannot open file");
	}

	TmpFile tmpfilename("normalizr");
	if (!tmpfilename) {
		ERR(2, filename, "Cannot create temporary file");
	}

	ofstream tmpfile(tmpfilename.str());
	if (!tmpfile) {
		ERR(2, filename, "Cannot open temporary file");
	}

	int ch;
	bool endswithnewline = false;
	ostringstream buffer;

	// Set up the state machine that will (selectively) copy the bytes from
	// the original file to the temporary.
	while ((ch = f.get()) != EOF) {
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
				buffer.put(ch);
				break;
			}

			case '\n': {
				// Discard any contents in the buffer,
				// effectively trimming trailing whitespace.
				endswithnewline = true;
				buffer.seekp(0);
				buffer.str("");
				buffer.clear();
				tmpfile.put(ch);
				break;
			}

			default: {
				// A printable character. Write the buffer,
				// reset it, and then write the character.
				endswithnewline = false;
				if (buffer.str().size() > 0) {
					tmpfile << buffer.str();
					buffer.seekp(0);
					buffer.str("");
					buffer.clear();
				}
				tmpfile.put(ch);
				break;
			}
		}
	}

	// Unix files always end with a newline character.
	if (!endswithnewline) {
		tmpfile.put('\n');
	}

	// Make sure everything was written and close files.
	tmpfile.flush();

	if (!tmpfile) {
		ERR(3, filename, "Operation failed");
	}

	f.close();
	tmpfile.close();

	// Copy the temporary file to its original location.
	std::ifstream src(tmpfilename.str());
	std::ofstream dst(filename);

	if (dst) {
		dst << src.rdbuf();
		dst.flush();
	}

	if (dst) {
		return 0;
	} else {
		ERR(3, filename, "Failed to update original file");
	}
}

int main(int argc, char* argv[]) {
	if (argc < 2) {
		usage(argc, argv);
	}

	int retval = 0;

	for (int i = 1; i < argc; i++) {
		retval = max(retval, normalize(argv[i]));
	}

	return retval;
}
